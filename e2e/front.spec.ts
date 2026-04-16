import { test, expect } from '@playwright/test';

// IDs et slugs stables basés sur les fixtures chargées en BDD
const PLANET_SLUG  = 'raccoons-of-asgard'; // Raccoons of Asgard (id: 97)
const PLANET_ID    = 97;                    // Raccoons of Asgard
const ASTRONAUT_ID = 412;                   // Thomas Dupont

test.describe('Planètes', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('affiche la liste des planètes avec classement', async ({ page }) => {
    await page.goto('/planets');
    // /planets peut rediriger vers /planets/archives si la saison active n'est pas dans les 3 onglets
    await expect(page).toHaveURL(/\/planets/);
    await expect(page.locator('.page-title')).toBeVisible();
  });

  test('affiche le détail d\'une planète', async ({ page }) => {
    await page.goto(`/planets/${PLANET_SLUG}`);
    await expect(page).toHaveURL(`/planets/${PLANET_SLUG}`);
    await expect(page.locator('.planet-title-glitch')).toBeVisible();
  });

  test('les onglets de saisons changent l\'URL', async ({ page }) => {
    await page.goto('/planets');
    const seasonLink = page.locator('a[href*="season="]').first();
    if (await seasonLink.count() > 0) {
      await seasonLink.click();
      await expect(page).toHaveURL(/season=\d+/);
      // La liste reste visible après changement de saison
      await expect(page.locator('.unit-card').first()).toBeVisible();
    }
  });

  test('la page archives est accessible', async ({ page }) => {
    await page.goto('/planets/archives');
    await expect(page).toHaveURL('/planets/archives');
    await expect(page.locator('body')).toBeVisible();
  });
});

test.describe('Astronautes', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('affiche la liste des astronautes', async ({ page }) => {
    await page.goto('/astronauts');
    await expect(page).toHaveURL('/astronauts');
    await expect(page.locator('.page-title')).toBeVisible();
    await expect(page.locator('.unit-card').first()).toBeVisible();
  });

  test('filtre par planète via l\'URL', async ({ page }) => {
    await page.goto(`/astronauts?planet=${PLANET_ID}`);
    await expect(page).toHaveURL(`/astronauts?planet=${PLANET_ID}`);
    await expect(page.locator('.unit-card').first()).toBeVisible();
  });

  test('filtre par planète via le bouton', async ({ page }) => {
    await page.goto('/astronauts');
    const planetBtn = page.locator('a[href*="planet="]').first();
    if (await planetBtn.count() > 0) {
      await planetBtn.click();
      await expect(page).toHaveURL(/planet=\d+/);
      // Toutes les planètes dans le filtre ont au moins un astronaute actif
      await expect(page.locator('.unit-card').first()).toBeVisible();
    }
  });

  test('filtre par squad Paris', async ({ page }) => {
    await page.goto('/astronauts?squad=paris');
    await expect(page).toHaveURL('/astronauts?squad=paris');
    await expect(page.locator('.unit-card').first()).toBeVisible();
  });

  test('filtre par squad via le bouton', async ({ page }) => {
    await page.goto('/astronauts');
    const squadBtn = page.locator('a[href*="squad="]').first();
    if (await squadBtn.count() > 0) {
      await squadBtn.click();
      await expect(page).toHaveURL(/squad=/);
    }
  });

  // La recherche est un filtre JS côté client (pas de paramètre URL)
  test('recherche par nom filtre les cartes visibles', async ({ page }) => {
    await page.goto('/astronauts');
    const input = page.locator('#astronaut-q');
    await expect(input).toBeVisible();
    await input.fill('Thomas');
    // Le filtre JS masque les cartes qui ne correspondent pas
    await expect(page.locator('.unit-card[data-name*="thomas"]')).toBeVisible();
  });

  test('recherche sans résultat masque toutes les cartes', async ({ page }) => {
    await page.goto('/astronauts');
    const input = page.locator('#astronaut-q');
    await expect(input).toBeVisible();
    await input.fill('zzz_inexistant_zzz');
    // Toutes les cartes doivent être masquées (display:none ou hidden)
    await expect(page.locator('.unit-card').first()).not.toBeVisible();
  });

  test('affiche le profil d\'un astronaute', async ({ page }) => {
    await page.goto(`/astronauts/${ASTRONAUT_ID}`);
    await expect(page).toHaveURL(`/astronauts/${ASTRONAUT_ID}`);
    await expect(page.locator('.name-glitch')).toBeVisible();
  });
});

test.describe('Profil', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('/profile redirige vers la fiche astronaute', async ({ page }) => {
    await page.goto('/profile');
    await expect(page).toHaveURL(/\/astronauts\/\d+/);
  });

  test('affiche le formulaire d\'édition', async ({ page }) => {
    await page.goto('/profile/edit');
    await expect(page).toHaveURL('/profile/edit');
    await expect(page.locator('form')).toBeVisible();
  });

  test('la navigation indique la page active', async ({ page }) => {
    await page.goto('/astronauts');
    await expect(page.locator('.nav-links a.active')).toBeVisible();
  });

  test('le lien profil nav est présent', async ({ page }) => {
    await page.goto('/planets');
    await expect(page.locator('.sys-user')).toBeVisible();
  });
});
