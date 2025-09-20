import { test, expect } from '@playwright/test';

test('User admin bisa login', async ({ page }) => {
  await page.goto('http://localhost:8000/admin/auth/login');
  await page.fill('[data-test="username"]', 'admin');
  await page.fill('[data-test="password"]', '12345');
  await page.click('[data-test="submit"]');
  await expect(page).toHaveURL("http://localhost:8000/admin/dashboard");

});

test('Customer bisa login', async ({ page }) => {
  await page.goto('http://localhost:8000/customer/auth/login');
  await page.fill('[data-test="username"]', '2025003');
  await page.fill('[data-test="password"]', '12345');
  await page.click('[data-test="submit"]');
  await expect(page).toHaveURL("http://localhost:8000/customer/dashboard");

});
