import type { FullConfig, Reporter, Suite, TestCase, TestResult } from '@playwright/test/reporter';
import fs from 'node:fs';
import path from 'node:path';

class E2EFileLogger implements Reporter {
  private readonly logPath = path.join(process.cwd(), 'storage/logs/e2e.log');

  private write(message: string): void {
    fs.mkdirSync(path.dirname(this.logPath), { recursive: true });
    fs.appendFileSync(this.logPath, `${new Date().toISOString()} ${message}\n`);
  }

  onBegin(config: FullConfig, suite: Suite): void {
    const baseUrl = config.projects[0]?.use?.baseURL ?? 'n/a';
    this.write(`E2E run started | total=${suite.allTests().length} | baseURL=${baseUrl}`);
  }

  onTestBegin(test: TestCase): void {
    this.write(`START ${test.titlePath().join(' > ')}`);
  }

  onTestEnd(test: TestCase, result: TestResult): void {
    this.write(`END ${test.titlePath().join(' > ')} | status=${result.status} | durationMs=${result.duration}`);
  }

  onEnd(): void {
    this.write('E2E run finished');
  }
}

export default E2EFileLogger;
