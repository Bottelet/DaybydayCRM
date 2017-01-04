<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class ForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');
    }

    /**
     * Test email validation on forgot password form
     */
    public function testForgotPasswordWithWrongEmail()
    {
        $this->visit('/')
        ->click('Forgot Your Password?')
            ->type('test@flarepoint.com', 'email')
            ->press('Send Password Reset Link')
            ->see('We can\'t find a user with that e-mail address.');;
    }

    /**
     * Test validation when empty on password form
     */
    public function testForgotPasswordInputIsEmpty()
    {
        $this->visit('/')
        ->click('Forgot Your Password?')
            ->type('', 'email')
            ->press('Send Password Reset Link')
            ->see('The email field is required.');
    }
}
