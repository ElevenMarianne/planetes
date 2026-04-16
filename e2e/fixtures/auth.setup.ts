import { test as setup, expect } from '@playwright/test';
import path from 'path';

const USER_AUTH  = path.join(__dirname, '../.auth/user.json');
const ADMIN_AUTH = path.join(__dirname, '../.auth/admin.json');

const USER_EMAIL  = 'jmartin@eleven-labs.com';
const ADMIN_EMAIL = 'tdupont@eleven-labs.com';

setup('authenticate as user', async ({ page }) => {
  await page.goto(`/test/login?email=${USER_EMAIL}`);
  await expect(page).toHaveURL(/\/planets/);
  await page.context().storageState({ path: USER_AUTH });
});

setup('authenticate as admin', async ({ page }) => {
  await page.goto(`/test/login?email=${ADMIN_EMAIL}`);
  await expect(page).toHaveURL(/\/planets/);
  await page.context().storageState({ path: ADMIN_AUTH });
});
