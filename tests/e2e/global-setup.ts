import { execSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { join } from 'node:path';

export default async function globalSetup() {
  const manifest = join(process.cwd(), 'public', 'build', 'manifest.json');
  if (!existsSync(manifest) || process.env.PLAYWRIGHT_REBUILD) {
    execSync('npm run build', { stdio: 'inherit' });
  }
  execSync('php artisan e2e:reset', { stdio: 'inherit' });
}
