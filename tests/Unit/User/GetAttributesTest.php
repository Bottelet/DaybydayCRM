<?php
namespace Tests\Unit\User;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetAttributesTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;

    public function setUp(): void
    {
        parent::setUp();
        $department = factory(Department::class)->create([
            'name' => 'Tiger'
        ]);
        $this->user = factory(User::class)->create([
            'name' => 'Eye of the'
        ]);
        $this->user->department()->sync([$department->id]);
    }

    /** @test */
    public function getNameAndDepartment()
    {
        $this->assertEquals("Eye of the (Tiger)", $this->user->name_and_department);
    }

    /** @test */
    public function getNameAndDepartmentWithEagerLoading()
    {
        $userWithEasgerLoading = User::whereName($this->user->name)->with('department')->first();
        $this->assertEquals("Eye of the (Tiger)", $userWithEasgerLoading->name_and_department_eager_loading);
    }

    /** @test */
    public function getDefaultAvatarWhenNoneIsSet()
    {
        $this->assertEquals('/images/default_avatar.jpg', $this->user->avatar);
    }

    /** @test */
    public function getPathWhenImageIsSet()
    {
        //Default is S3, but same logic for local driver
        \Config::set('filesystems.default', "local");
        $this->user->image_path = "tiger.jpg";

        $this->assertEquals('/storage/tiger.jpg', $this->user->avatar);
    }
}
