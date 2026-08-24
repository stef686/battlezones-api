import { execSync } from 'node:child_process';

/**
 * Put the world back the way each browser test expects to find it.
 *
 * The seeder resets the fixtures the flows consume — a scored Game, a claimed
 * Invite, a Captain who has entered. The cache goes with it because the login
 * throttle lives there: a suite that signs the same Player in several times is
 * indistinguishable, to a rate limiter, from someone guessing their password.
 */
export function resetWorld(): void {
    execSync('php artisan db:seed --class=EndToEndSeeder --no-interaction && php artisan cache:clear', {
        cwd: '..',
        stdio: 'pipe',
    });
}
