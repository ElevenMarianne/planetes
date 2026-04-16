import { test, expect } from '@playwright/test';

// Suffixe unique pour éviter les conflits entre runs
const TS = Date.now().toString().slice(-6);

// IDs stables depuis les fixtures BDD
const ACTIVITY_TYPE_ID      = 331;  // Article de blog (solo) — 75pts — BOTH
const ACTIVITY_TYPE_EDIT_ID = 327;  // Challenge — 1ère place (pour test édition)
const ASTRONAUT_ID          = 412;  // Thomas Dupont
const TROPHY_ID             = 11;   // Planète championne de la saison
const PLANET_ID             = 97;   // Raccoons of Asgard

test.describe('Back-office — Formulaires complets', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' });

  // ─── Types d'activité ─────────────────────────────────────────────────────

  test('créer un type d\'activité', async ({ page }) => {
    await page.goto('/back/activity-types/new');
    await page.locator('input[name="name"]').fill(`Type E2E ${TS}`);
    await page.locator('input[name="basePoints"]').fill('42');
    await page.locator('input[name="isActive"]').check();
    await page.getByRole('button', { name: /CRÉER LE TYPE/i }).click();

    await expect(page).toHaveURL('/back/activity-types');
    await expect(page.locator('.flash-success')).toContainText("Type d'activité créé");
  });

  test('modifier un type d\'activité existant', async ({ page }) => {
    await page.goto(`/back/activity-types/${ACTIVITY_TYPE_EDIT_ID}/edit`);
    await expect(page.locator('input[name="name"]')).not.toBeEmpty();

    const descField = page.locator('textarea[name="description"]');
    await descField.fill(`Modifié par E2E ${TS}`);
    await page.getByRole('button', { name: /METTRE À JOUR/i }).click();

    await expect(page).toHaveURL('/back/activity-types');
    await expect(page.locator('.flash-success')).toContainText('Type mis à jour');
  });

  test('toggler l\'état actif/inactif d\'un type', async ({ page }) => {
    await page.goto('/back/activity-types');
    const toggleForm = page.locator('form[action*="/toggle"]').first();
    const toggleBtn  = toggleForm.locator('button');
    const initialText = (await toggleBtn.textContent()) ?? '';
    await toggleBtn.click();

    await expect(page).toHaveURL('/back/activity-types');
    await expect(page.locator('.flash-success')).toBeVisible();
    // Vérifier que l'état a bien basculé (le bouton d'origine a changé de libellé)
    const newText = await page.locator('form[action*="/toggle"]').first().locator('button').textContent();
    expect(newText?.trim()).not.toBe(initialText.trim());
  });

  // ─── Saisons ──────────────────────────────────────────────────────────────

  test('créer une saison', async ({ page }) => {
    await page.goto('/back/seasons/new');
    await page.locator('input[name="name"]').fill(`Saison E2E ${TS}`);
    await page.locator('input[name="startDate"]').fill('2030-09-01');
    await page.locator('input[name="endDate"]').fill('2031-06-30');
    await page.getByRole('button', { name: /CRÉER LA SAISON/i }).click();

    await expect(page).toHaveURL('/back/seasons');
    await expect(page.locator('.flash-success')).toContainText('Saison créée');
  });

  // ─── Trophées ─────────────────────────────────────────────────────────────

  test('créer un trophée (sans image)', async ({ page }) => {
    await page.goto('/back/trophies/new');
    await page.locator('input[name="name"]').fill(`Trophée E2E ${TS}`);
    await page.locator('textarea[name="description"]').fill('Trophée créé par les tests E2E automatisés.');
    await page.getByRole('button', { name: /CRÉER LE TROPHÉE/i }).click();

    await expect(page).toHaveURL('/back/trophies');
    await expect(page.locator('.flash-success')).toContainText('Trophée créé');
  });

  test('attribuer un trophée à un astronaute', async ({ page }) => {
    await page.goto('/back/trophies/award');
    await page.locator('select[name="trophy"]').selectOption(String(TROPHY_ID));
    // target=astronaut est sélectionné par défaut
    await page.locator('#astronaut-select').selectOption(String(ASTRONAUT_ID));
    await page.getByRole('button', { name: /ATTRIBUER LE TROPHÉE/i }).click();

    await expect(page).toHaveURL('/back/trophies');
    await expect(page.locator('.flash-success')).toContainText('Thomas Dupont');
  });

  test('attribuer un trophée à une planète', async ({ page }) => {
    await page.goto('/back/trophies/award');
    await page.locator('select[name="trophy"]').selectOption(String(TROPHY_ID));
    // Basculer sur "Une Planète"
    await page.locator('#tab-planet').click();
    await expect(page.locator('#planet-select')).toBeEnabled();
    await page.locator('#planet-select').selectOption(String(PLANET_ID));
    await page.getByRole('button', { name: /ATTRIBUER LE TROPHÉE/i }).click();

    await expect(page).toHaveURL('/back/trophies');
    await expect(page.locator('.flash-success')).toContainText('Raccoons of Asgard');
  });

  // ─── Attribution de points ────────────────────────────────────────────────

  test('attribuer des points à un astronaute', async ({ page }) => {
    await page.goto('/back/activities/new');
    // Sélectionner le type d'activité
    await page.locator('#activity_form_type').selectOption(String(ACTIVITY_TYPE_ID));
    // Le select astronautes est caché (UI custom) — on le sélectionne en force
    await page.locator('#activity_form_astronauts').selectOption(
      { value: String(ASTRONAUT_ID) },
      { force: true }
    );
    await page.getByRole('button', { name: /ATTRIBUER LES POINTS/i }).click();

    await expect(page).toHaveURL('/back/activities');
    await expect(page.locator('.flash-success')).toContainText('astronaute');
  });

  // ─── Astronautes ──────────────────────────────────────────────────────────

  test('créer un astronaute', async ({ page }) => {
    await page.goto('/back/astronauts/new');
    await page.locator('#astronaut_firstName').fill('Test');
    await page.locator('#astronaut_lastName').fill(`E2E${TS}`);
    await page.locator('#astronaut_email').fill(`test.e2e.${TS}@eleven-labs.com`);
    await page.locator('#astronaut_planet').selectOption(String(PLANET_ID));
    await page.locator('#astronaut_squad').selectOption('paris');
    await page.locator('#astronaut_roles_0').check(); // ROLE_USER
    await page.locator('#astronaut_isActive').check();
    await page.getByRole('button', { name: /ENREGISTRER/i }).click();

    await expect(page).toHaveURL('/back/astronauts');
    await expect(page.locator('.flash-success')).toContainText('Astronaute créé');
  });
});
