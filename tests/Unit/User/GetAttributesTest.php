<?php

namespace Tests\Unit\User;

use App\Models\Department;
use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class GetAttributesTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_name_and_department()
    {
        /* Arrange */
        $department = Department::factory()->create([
            'name' => 'Tiger',
        ]);
        $this->user = User::factory()->create([
            'name' => 'Eye of the',
        ]);
        $this->user->department()->sync([$department->id]);

        /* Act */
        $nameAndDepartment = $this->user->name_and_department;

        /* Assert */
        $this->assertEquals('Eye of the (Tiger)', $nameAndDepartment);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_name_and_department_with_eager_loading()
    {
        /* Arrange */
        $department = Department::factory()->create([
            'name' => 'Tiger',
        ]);
        $this->user = User::factory()->create([
            'name' => 'Eye of the',
        ]);
        $this->user->department()->sync([$department->id]);

        /* Act */
        $userWithEagerLoading = User::whereName($this->user->name)->with('department')->first();
        $nameAndDepartment    = $userWithEagerLoading->name_and_department_eager_loading;

        /* Assert */
        $this->assertEquals('Eye of the (Tiger)', $nameAndDepartment);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_name_without_department_with_eager_loading()
    {
        /* Arrange */
        $this->user = User::factory()->create([
            'name' => 'Eye of the',
        ]);

        /* Act */
        $userWithEagerLoading = User::whereName($this->user->name)->with('department')->first();
        $nameAndDepartment    = $userWithEagerLoading->name_and_department_eager_loading;

        /* Assert */
        $this->assertEquals('Eye of the', $nameAndDepartment);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_default_avatar_when_none_is_set()
    {
        /* Arrange */
        $this->user = User::factory()->create([
            'name' => 'Eye of the',
        ]);

        /* Act */
        $avatar = $this->user->avatar;

        /* Assert */
        $this->assertEquals('/images/default_avatar.jpg', $avatar);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_path_when_image_is_set()
    {
        /* Arrange */
        $this->user = User::factory()->create([
            'name' => 'Eye of the',
        ]);
        Config::set('filesystems.default', 'local');
        $this->user->image_path = 'tiger.jpg';

        /* Act */
        $avatar = $this->user->avatar;

        /* Assert */
        $this->assertEquals('/storage/tiger.jpg', $avatar);
    }
}
