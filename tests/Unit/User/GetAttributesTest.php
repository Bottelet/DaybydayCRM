<?php

namespace Tests\Unit\User;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetAttributesTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_name_and_department()
    {
        $department = factory(Department::class)->create([
            'name' => 'Tiger',
        ]);
        $this->user = factory(User::class)->create([
            'name' => 'Eye of the',
        ]);
        $this->user->department()->sync([$department->id]);

        $this->assertEquals('Eye of the (Tiger)', $this->user->name_and_department);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_name_and_department_with_eager_loading()
    {
        $department = factory(Department::class)->create([
            'name' => 'Tiger',
        ]);
        $this->user = factory(User::class)->create([
            'name' => 'Eye of the',
        ]);
        $this->user->department()->sync([$department->id]);

        $userWithEasgerLoading = User::whereName($this->user->name)->with('department')->first();
        $this->assertEquals('Eye of the (Tiger)', $userWithEasgerLoading->name_and_department_eager_loading);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_default_avatar_when_none_is_set()
    {
        $this->user = factory(User::class)->create([
            'name' => 'Eye of the',
        ]);

        $this->assertEquals('/images/default_avatar.jpg', $this->user->avatar);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_path_when_image_is_set()
    {
        $this->user = factory(User::class)->create([
            'name' => 'Eye of the',
        ]);

        // Default is S3, but same logic for local driver
        \Config::set('filesystems.default', 'local');
        $this->user->image_path = 'tiger.jpg';

        $this->assertEquals('/storage/tiger.jpg', $this->user->avatar);
    }
}
