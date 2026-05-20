export function interpolateRoutePath(rawPath: string): string {
  return rawPath.replace(/\{([^}]+)\??\}/g, (_fullMatch, token: string) => {
    const key = token.toLowerCase();
    if (key === 'external_id' || key.endsWith('_external_id') || key === 'uuid' || key.endsWith('_uuid')) {
      return '00000000-0000-0000-0000-000000000001';
    }

    if (key === 'query' || key.endsWith('_query')) {
      return 'search-term';
    }

    if (key === 'type' || key.endsWith('_type')) {
      return 'task';
    }

    return '1';
  });
}

export function malformedInterpolatedRoutePath(rawPath: string): string {
  return rawPath.replace(/\{([^}]+)\??\}/g, 'invalid-@@@');
}
