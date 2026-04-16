import { test, expect } from '@playwright/test';

test.describe('Back-office', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' });

  // ─── Dashboard ────────────────────────────────────────────────────────────

  test('dashboard affiche les compteurs et la saison active', async ({ page }) => {
    await page.goto('/back');
    await expect(page).toHaveURL('/back');
    await expect(page.locator('.page-title')).toBeVisible();
    await expect(page.locator('body')).toContainText('SAISON');
  });

  // ─── Activités ────────────────────────────────────────────────────────────

  test('liste des activités de la saison courante', async ({ page }) => {
    await page.goto('/back/activities');
    await expect(page).toHaveURL('/back/activities');
    await expect(page.locator('.page-title')).toBeVisible();
  });

  test('formulaire d\'attribution de points s\'affiche', async ({ page }) => {
    await page.goto('/back/activities/new');
    await expect(page).toHaveURL('/back/activities/new');
    await expect(page.locator('form')).toBeVisible();
    // Les champs essentiels sont présents
    await expect(page.locator('select, input').first()).toBeVisible();
  });

  // ─── Types d'activité ─────────────────────────────────────────────────────

  test('liste des types d\'activité avec tableau', async ({ page }) => {
    await page.goto('/back/activity-types');
    await expect(page).toHaveURL('/back/activity-types');
    await expect(page.locator('.page-title')).toBeVisible();
    await expect(page.locator('.back-table')).toBeVisible();
  });

  test('formulaire de création d\'un type d\'activité', async ({ page }) => {
    await page.goto('/back/activity-types/new');
    await expect(page).toHaveURL('/back/activity-types/new');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('[name="name"]')).toBeVisible();
    await expect(page.locator('[name="basePoints"]')).toBeVisible();
  });

  test('formulaire d\'édition d\'un type d\'activité existant', async ({ page }) => {
    await page.goto('/back/activity-types/327/edit');
    await expect(page).toHaveURL('/back/activity-types/327/edit');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('[name="name"]')).not.toBeEmpty();
  });

  // ─── Saisons ──────────────────────────────────────────────────────────────

  test('liste des saisons', async ({ page }) => {
    await page.goto('/back/seasons');
    await expect(page).toHaveURL('/back/seasons');
    await expect(page.locator('.page-title')).toBeVisible();
    await expect(page.locator('body')).toContainText('SAISONS');
  });

  test('formulaire de création de saison', async ({ page }) => {
    await page.goto('/back/seasons/new');
    await expect(page).toHaveURL('/back/seasons/new');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('[name="name"]')).toBeVisible();
    await expect(page.locator('[name="startDate"]')).toBeVisible();
    await expect(page.locator('[name="endDate"]')).toBeVisible();
  });

  // ─── Astronautes (back) ────────────────────────────────────────────────────

  test('liste des astronautes en back-office', async ({ page }) => {
    await page.goto('/back/astronauts');
    await expect(page).toHaveURL('/back/astronauts');
    await expect(page.locator('.page-title')).toBeVisible();
    await expect(page.locator('.back-table')).toBeVisible();
  });

  // ─── Trophées ─────────────────────────────────────────────────────────────

  test('liste des trophées', async ({ page }) => {
    await page.goto('/back/trophies');
    await expect(page).toHaveURL('/back/trophies');
    await expect(page.locator('.page-title')).toBeVisible();
    // Le trophée de fixture (affiché en uppercase via |upper dans le template)
    await expect(page.locator('body')).toContainText('PLANÈTE CHAMPIONNE');
  });

  test('formulaire d\'attribution de trophée', async ({ page }) => {
    await page.goto('/back/trophies/award');
    await expect(page).toHaveURL('/back/trophies/award');
    await expect(page.locator('form')).toBeVisible();
    // Sélecteurs de trophée, cible et destinataire
    await expect(page.locator('select').first()).toBeVisible();
  });
});