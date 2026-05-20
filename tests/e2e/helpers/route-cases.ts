import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

export interface RouteCase {
  method: HttpMethod;
  path: string;
  dynamic: boolean;
  middleware: string[];
}

const SUPPORTED_METHODS = new Set<HttpMethod>(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
let cachedWebRouteCases: RouteCase[] | null = null;
let cachedPhpUnitHttpCalls: RouteCase[] | null = null;

function normalizePath(rawPath: string): string {
  const withoutDomain = rawPath.replace(/^https?:\/\/[^/]+/i, '');
  const trimmed = withoutDomain.trim();
  if (!trimmed) {
    return '/';
  }

  return trimmed.startsWith('/') ? trimmed : `/${trimmed}`;
}

function isStaticLiteralPath(rawPath: string): boolean {
  return !rawPath.includes('$') && !rawPath.includes('->') && !rawPath.includes('::');
}

function shouldSkipPath(rawPath: string): boolean {
  return !rawPath.startsWith('/') || !isStaticLiteralPath(rawPath);
}

function inferMiddlewareFromPath(normalizedPath: string): string[] {
  if (
    normalizedPath === '/login' ||
    normalizedPath === '/register' ||
    normalizedPath === '/password/reset' ||
    normalizedPath === '/password/email'
  ) {
    return ['web'];
  }

  return ['auth', 'web'];
}

function normalizeMiddleware(raw: unknown): string[] {
  if (Array.isArray(raw)) {
    return raw.map((item) => String(item));
  }

  if (typeof raw === 'string') {
    return raw
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean);
  }

  return [];
}

function expandResource(resource: string): RouteCase[] {
  const base = normalizePath(resource);

  return [
    { method: 'GET', path: base, dynamic: false, middleware: ['web'] },
    { method: 'GET', path: `${base}/create`, dynamic: false, middleware: ['web'] },
    { method: 'POST', path: base, dynamic: false, middleware: ['web'] },
    { method: 'GET', path: `${base}/{resource}`, dynamic: true, middleware: ['web'] },
    { method: 'GET', path: `${base}/{resource}/edit`, dynamic: true, middleware: ['web'] },
    { method: 'PUT', path: `${base}/{resource}`, dynamic: true, middleware: ['web'] },
    { method: 'PATCH', path: `${base}/{resource}`, dynamic: true, middleware: ['web'] },
    { method: 'DELETE', path: `${base}/{resource}`, dynamic: true, middleware: ['web'] },
  ];
}

function fromArtisanRouteList(): RouteCase[] {
  const json = execSync('php artisan route:list --json', { encoding: 'utf8', stdio: ['ignore', 'pipe', 'inherit'] });
  let parsed: Array<Record<string, unknown>>;
  try {
    parsed = JSON.parse(json) as Array<Record<string, unknown>>;
  } catch (error) {
    const parseError = error instanceof Error ? error.message : String(error);
    throw new Error(`Unable to parse php artisan route:list JSON output (${parseError}): ${json.slice(0, 500)}`);
  }

  const routeCases: RouteCase[] = [];

  for (const route of parsed) {
    const middleware = normalizeMiddleware(route.middleware);
    if (!middleware.includes('web')) {
      continue;
    }

    const rawUri = String(route.uri ?? '').trim();
    if (!rawUri || rawUri.startsWith('_debugbar') || rawUri.startsWith('up')) {
      continue;
    }

    const methods = String(route.method ?? '')
      .split('|')
      .map((method) => method.trim().toUpperCase())
      .filter((method): method is HttpMethod => SUPPORTED_METHODS.has(method as HttpMethod));

    for (const method of methods) {
      const normalizedPath = normalizePath(rawUri);
      routeCases.push({
        method,
        path: normalizedPath,
        dynamic: normalizedPath.includes('{'),
        middleware,
      });
    }
  }

  return routeCases;
}

function fromWebPhpFallback(): RouteCase[] {
  const webPhp = fs.readFileSync(path.join(process.cwd(), 'routes/web.php'), 'utf8');
  const routeCases: RouteCase[] = [];
  const verbMatches = webPhp.matchAll(/Route::(get|post|put|patch|delete)\(\s*'([^']+)'/gi);

  for (const match of verbMatches) {
    const method = match[1].toUpperCase() as HttpMethod;
    if (!SUPPORTED_METHODS.has(method)) {
      continue;
    }

    const normalizedPath = normalizePath(match[2]);
    routeCases.push({
      method,
      path: normalizedPath,
      dynamic: normalizedPath.includes('{'),
      middleware: ['auth', 'web'],
    });
  }

  const resourceMatches = webPhp.matchAll(/Route::resource\(\s*'([^']+)'/g);
  for (const match of resourceMatches) {
    routeCases.push(...expandResource(match[1]));
  }

  routeCases.push(
    { method: 'GET', path: '/login', dynamic: false, middleware: ['web'] },
    { method: 'POST', path: '/login', dynamic: false, middleware: ['web'] },
    { method: 'POST', path: '/logout', dynamic: false, middleware: ['auth', 'web'] },
    { method: 'GET', path: '/register', dynamic: false, middleware: ['web'] },
    { method: 'POST', path: '/register', dynamic: false, middleware: ['web'] },
    { method: 'GET', path: '/password/reset', dynamic: false, middleware: ['web'] },
    { method: 'POST', path: '/password/email', dynamic: false, middleware: ['web'] },
  );

  return routeCases;
}

function dedupe(routeCases: RouteCase[]): RouteCase[] {
  const seen = new Set<string>();
  const deduped: RouteCase[] = [];

  for (const routeCase of routeCases) {
    const key = `${routeCase.method} ${routeCase.path}`;
    if (seen.has(key)) {
      continue;
    }

    seen.add(key);
    deduped.push(routeCase);
  }

  return deduped;
}

export function loadWebRouteCases(): RouteCase[] {
  if (cachedWebRouteCases !== null) {
    return cachedWebRouteCases;
  }

  try {
    cachedWebRouteCases = dedupe(fromArtisanRouteList());
  } catch {
    cachedWebRouteCases = dedupe(fromWebPhpFallback());
  }

  return cachedWebRouteCases;
}

export function loadPhpUnitHttpCalls(): RouteCase[] {
  if (cachedPhpUnitHttpCalls !== null) {
    return cachedPhpUnitHttpCalls;
  }

  const testsRoot = path.join(process.cwd(), 'tests');
  const files: string[] = [];
  const stack = [testsRoot];

  while (stack.length > 0) {
    const current = stack.pop();
    if (!current) {
      continue;
    }
    const entries = fs.readdirSync(current, { withFileTypes: true });
    for (const entry of entries) {
      const fullPath = path.join(current, entry.name);
      if (entry.isDirectory()) {
        stack.push(fullPath);
        continue;
      }

      if (entry.isFile() && entry.name.endsWith('Test.php')) {
        files.push(fullPath);
      }
    }
  }

  const routeCases: RouteCase[] = [];

  for (const filePath of files) {
    const content = fs.readFileSync(filePath, 'utf8');
    const directMatches = content.matchAll(
      /\$this->(get|post|put|patch|delete|getJson|postJson|putJson|patchJson|deleteJson)\(\s*['"]([^'"]+)['"]/g
    );

    for (const match of directMatches) {
      const method = match[1].replace(/json/i, '').toUpperCase() as HttpMethod;
      if (!SUPPORTED_METHODS.has(method)) {
        continue;
      }

      const rawPath = match[2];
      if (shouldSkipPath(rawPath)) {
        continue;
      }

      const normalizedPath = normalizePath(rawPath);
      routeCases.push({
        method,
        path: normalizedPath,
        dynamic: normalizedPath.includes('{'),
        middleware: inferMiddlewareFromPath(normalizedPath),
      });
    }

    const jsonMatches = content.matchAll(/\$this->json\(\s*['"]([A-Z]+)['"]\s*,\s*['"]([^'"]+)['"]/g);
    for (const match of jsonMatches) {
      const method = match[1].toUpperCase() as HttpMethod;
      if (!SUPPORTED_METHODS.has(method)) {
        continue;
      }

      const rawPath = match[2];
      if (shouldSkipPath(rawPath)) {
        continue;
      }

      const normalizedPath = normalizePath(rawPath);
      routeCases.push({
        method,
        path: normalizedPath,
        dynamic: normalizedPath.includes('{'),
        middleware: inferMiddlewareFromPath(normalizedPath),
      });
    }
  }

  cachedPhpUnitHttpCalls = dedupe(routeCases);

  return cachedPhpUnitHttpCalls;
}
