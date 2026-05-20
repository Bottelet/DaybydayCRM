# Composer Update Workflow

This workflow allows you to manage composer dependencies directly from GitHub Actions, perfect for updating packages from your mobile device.

## How to Use

1. **Navigate to Actions Tab**
   - Go to your repository on GitHub
   - Click on the "Actions" tab
   - Find "Composer Update Manager" in the left sidebar

2. **Run the Workflow**
   - Click on "Composer Update Manager"
   - Click the "Run workflow" button
   - Select your options (see below)
   - Click "Run workflow"

## Update Types

### `update` (Default)
Basic composer update. Updates packages based on version constraints in composer.json.
- Use this for routine updates
- Can target specific packages

### `update-all`
Updates all packages in the project.
- Updates everything to latest compatible versions
- Good for major dependency updates

### `update-with-deps`
Updates specified packages along with their dependencies.
- More thorough than basic update
- Ensures dependencies are also updated

### `update-with-all-deps`
Updates packages with all their dependencies, including dev dependencies.
- Most comprehensive update
- Use when you need to resolve complex dependency chains

### `install`
Runs `composer install` to sync with composer.lock.
- Use when you just need to install existing locked versions
- Good for debugging

### `validate`
Validates composer.json and composer.lock files.
- Checks for syntax errors
- Verifies lock file is in sync

### `outdated`
Shows which packages have newer versions available.
- Read-only operation
- No changes committed
- Results shown in workflow summary

### `bump-deps`
Bumps dependency versions in composer.json.
- Updates version constraints
- Useful before doing updates

## Options

### Packages (Optional)
Specify which packages to update (space-separated).
- Example: `laravel/framework symfony/console`
- Leave empty to update all packages

### Ignore Platform Requirements
Bypass PHP version and extension checks.
- Use with caution
- Helpful for testing

### Prefer Lowest
Install lowest versions of dependencies.
- Useful for testing minimum requirements
- Not recommended for production

### Prefer Stable
Prefer stable versions over dev versions.
- Enabled by default
- Recommended for production

## Examples

### Update Laravel Framework Only
- Update Type: `update`
- Packages: `laravel/framework`

### Check What's Outdated
- Update Type: `outdated`

### Update All Dependencies
- Update Type: `update-all`
- Prefer Stable: ✓

### Update Specific Package with Dependencies
- Update Type: `update-with-deps`
- Packages: `yajra/laravel-datatables-oracle`

## After Running

The workflow will:
1. Run the selected composer command
2. Show changes in the workflow summary
3. Automatically commit and push changes (except for `outdated` and `validate`)
4. Include `[skip ci]` in commit message to avoid triggering other workflows

## Mobile Usage

This workflow is optimized for mobile use:
1. Open GitHub mobile app or mobile browser
2. Navigate to Actions → Composer Update Manager
3. Tap "Run workflow"
4. Select your options from dropdowns
5. Tap "Run workflow" to execute

The workflow will handle everything automatically and push changes back to your branch.
