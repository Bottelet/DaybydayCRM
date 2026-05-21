<?php

namespace App\Console\Commands;

use App\Enums\RoleType;
use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    public const DEFAULT_LANGUAGE = 'en';

    protected $signature = 'daybyday:create-admin {--name=} {--email=} {--password=}';

    protected $description = 'Create an admin user with all required dependencies (settings, owner role, management department). Safe to run on both fresh and seeded databases.';

    public function getHelp(): string
    {
        $help = parent::getHelp();

        $help .= "\n<fg=green>EXAMPLES</>\n";
        $help .= "  Interactive mode (prompts for missing inputs):\n";
        $help .= "    <fg=cyan>\$ php artisan user:create-admin</>\n\n";

        $help .= "  Non-interactive mode (Ansible-friendly, all parameters provided):\n";
        $help .= "    <fg=cyan>\$ php artisan user:create-admin --name=\"John Doe\" --email=\"john@example.com\" --password=\"SecureP@ss123\"</>\n\n";

        $help .= "  Mixed mode (prompt for password only):\n";
        $help .= "    <fg=cyan>\$ php artisan user:create-admin --name=\"Jane Smith\" --email=\"jane@example.com\"</>\n\n";

        $help .= "<fg=green>NOTES</>\n";
        $help .= "  • This command is safe to run multiple times on fresh databases.\n";
        $help .= "  • On seeded databases, it reuses existing Settings, owner Role, and Management Department records.\n";
        $help .= "  • No duplicate users will be created; attempting to create an existing email will fail safely.\n";

        return $help;
    }

    public function handle(): int
    {
        $this->newLine();
        $this->info('🔧 Creating admin user...');
        $this->newLine();

        // Resolve missing options interactively (skip prompts in non-interactive mode)
        $interactive = $this->input->isInteractive();
        $name        = $this->option('name') ?: ($interactive ? $this->ask('What is the admin name?') : null);
        $email       = $this->option('email') ?: ($interactive ? $this->ask('What is the admin email?') : null);
        $password    = $this->option('password') ?: ($interactive ? $this->secret('What is the admin password?') : null);

        // Validate inputs (accept ?string to handle null from ask()/secret() gracefully)
        if ( ! $this->validateInputs($name, $email, $password)) {
            return Command::FAILURE;
        }

        try {
            DB::transaction(function () use ($name, $email, $password) {
                if (User::where('email', $email)->exists()) {
                    throw new \RuntimeException('exists');
                }

                $this->ensureDependencies();
                $user = $this->createAdminUser($name, $email, $password);

                $department = Department::where('name', Department::MANAGEMENT)->first();
                if ($department) {
                    $user->department()->syncWithoutDetaching([$department->id]);
                    $this->line('   ✓ Attached user to Management department');
                }

                $role = Role::where('name', RoleType::OWNER->value)->first();
                if ($role) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                    $this->line('   ✓ Attached owner role');
                }
            });
        } catch (\RuntimeException $e) {
            $this->error("❌ User with email '{$email}' already exists.");

            return Command::FAILURE;
        } catch (QueryException $e) {
            $this->error('❌ Admin creation failed safely.');

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info("✅ Admin user '{$email}' created successfully!");

        return Command::SUCCESS;
    }

    /**
     * Validate input values.
     */
    private function validateInputs(?string $name, ?string $email, ?string $password): bool
    {
        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name'     => 'required|string|min:2',
                'email'    => 'required|email',
                'password' => 'required|string|min:8',
            ]
        );

        if ($validator->fails()) {
            $this->error('❌ Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("   • {$error}");
            }

            return false;
        }

        return true;
    }

    /**
     * Ensure required dependencies exist using firstOrCreate patterns.
     */
    private function ensureDependencies(): void
    {
        $this->info('Setting up dependencies...');

        // Ensure Settings exist - find by any existing row or create one
        $setting = Setting::first();
        if ( ! $setting) {
            $setting = Setting::create([
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'country'        => 'US',
                'company'        => 'Media',
                'max_users'      => 50,
                'vat'            => 0,
                'currency'       => 'USD',
                'language'       => self::DEFAULT_LANGUAGE,
            ]);
            $this->line('   + Created default settings');
        } else {
            $this->line('   ✓ Settings already exist');
        }

        // Ensure owner Role exists
        $role = Role::firstOrCreate(
            ['name' => RoleType::OWNER->value],
            [
                'display_name' => RoleType::OWNER->label(),
                'description'  => 'Full system owner',
                'external_id'  => (string) Str::uuid(),
            ]
        );
        if ($role->wasRecentlyCreated) {
            $this->line('   + Created owner role');
        } else {
            $this->line('   ✓ Owner role already exists');
        }

        // Ensure Management Department exists
        $department = Department::firstOrCreate(
            ['name' => Department::MANAGEMENT],
            ['external_id' => (string) Str::uuid()]
        );
        if ($department->wasRecentlyCreated) {
            $this->line('   + Created Management department');
        } else {
            $this->line('   ✓ Management department already exists');
        }

        $this->newLine();
    }

    /**
     * Create the admin user.
     */
    private function createAdminUser(string $name, string $email, string $password): User
    {
        return User::create([
            'name'        => $name,
            'email'       => $email,
            'password'    => Hash::make($password),
            'external_id' => (string) Str::uuid(),
            'language'    => self::DEFAULT_LANGUAGE,
        ]);
    }
}
