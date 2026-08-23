/**
 * Fails when a built bundle contains anything that should never be public.
 *
 * Everything in the bundle is readable by anyone who loads the app, so a
 * secret that reaches it is already leaked: this runs in CI against `dist`
 * so an accidentally-inlined key fails the build rather than shipping.
 */
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';

const FORBIDDEN = [
    { name: 'Laravel app key', pattern: /base64:[A-Za-z0-9+/]{40,}={0,2}/ },
    { name: 'private key block', pattern: /-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/ },
    { name: 'AWS access key id', pattern: /\bAKIA[0-9A-Z]{16}\b/ },
    { name: 'database password variable', pattern: /\bDB_PASSWORD\b/ },
    { name: 'mail password variable', pattern: /\bMAIL_PASSWORD\b/ },
    { name: 'non-public env variable', pattern: /\bimport\.meta\.env\.(?!VITE_)[A-Z_]+/ },
];

function* filesIn(directory) {
    for (const entry of readdirSync(directory)) {
        const path = join(directory, entry);

        if (statSync(path).isDirectory()) {
            yield* filesIn(path);
            continue;
        }

        yield path;
    }
}

const distribution = process.argv[2] ?? 'dist';
const failures = [];

for (const path of filesIn(distribution)) {
    const contents = readFileSync(path, 'utf8');

    for (const { name, pattern } of FORBIDDEN) {
        if (pattern.test(contents)) {
            failures.push(`${path}: ${name}`);
        }
    }
}

if (failures.length > 0) {
    console.error('Secrets found in the built bundle:');
    failures.forEach((failure) => console.error(`  - ${failure}`));
    process.exit(1);
}

console.log(`No secrets found in ${distribution}.`);
