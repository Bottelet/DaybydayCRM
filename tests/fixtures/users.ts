export const TEST_USERS = {
  owner: { email: 'owner@test.local', password: 'password' },
  admin: { email: 'admin@test.local', password: 'password' },
  manager: { email: 'manager@test.local', password: 'password' },
  employee: { email: 'employee@test.local', password: 'password' },
} as const;

export const SEED_CLIENT_NAME = 'Playwright Seed Client';
export const SEED_CLIENT_EXTERNAL_ID = '1dcad188-4c47-4939-9f0a-fb6802ef4f0d';

export const SEED_LEAD_TITLES = [
  'Sell Item',
  'Contact Client about new offer',
  'Client wants to know more about item',
] as const;
