import { test, expect } from '@playwright/test';

test.describe('Contrôle d\'accès', () => {
  test('/ redirige vers /login sans authentification', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/login/);
  });

  test('/planets redirige vers /login sans authentification', async ({ page }) => {
    await page.goto('/planets');
    await expect(page).toHaveURL(/\/login/);
  });

  test('/astronauts redirige vers /login sans authentification', async ({ page }) => {
    await page.goto('/astronauts');
    await expect(page).toHaveURL(/\/login/);
  });

  test('/profile redirige vers /login sans authentification', async ({ page }) => {
    await page.goto('/profile/edit');
    await expect(page).toHaveURL(/\/login/);
  });

  test('/back redirige vers /login sans authentification', async ({ page }) => {
    await page.goto('/back');
    await expect(page).toHaveURL(/\/login/);
  });

  test.describe('utilisateur connecté (ROLE_USER)', () => {
    test.use({ storageState: 'e2e/.auth/user.json' });

    test('/ redirige vers /planets', async ({ page }) => {
      await page.goto('/');
      // /planets peut rediriger vers /planets/archives si la saison active n'est pas dans les 3 onglets
      await expect(page).toHaveURL(/\/planets/);
    });

    test('/profile redirige vers la fiche astronaute', async ({ page }) => {
      await page.goto('/profile');
      await expect(page).toHaveURL(/\/astronauts\/\d+/);
    });

    test('/back retourne 403 pour un non-admin', async ({ page }) => {
      const response = await page.goto('/back');
      expect(response?.status()).toBe(403);
    });
  });

  test.describe('admin connecté (ROLE_ADMIN)', () => {
    test.use({ storageState: 'e2e/.auth/admin.json' });

    test('/back est accessible', async ({ page }) => {
      await page.goto('/back');
      await expect(page).toHaveURL('/back');
      await expect(page.locator('body')).toBeVisible();
    });
  });
});
