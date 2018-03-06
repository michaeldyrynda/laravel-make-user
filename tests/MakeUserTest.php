<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class MakeUserTest extends TestCase
{
    /** @test */
    public function it_requires_a_valid_email_address()
    {
        Artisan::call('make:user', ['--email' => 'invalidemail']);

        $this->assertFalse(User::where('email', 'invalidemail')->exists());
    }

    /** @test */
    public function it_requires_a_unique_email_address()
    {
        User::create(['name' => 'Adam Wathan', 'email' => 'adamwathan@example.com', 'password' => '']);

        $exitCode = Artisan::call('make:user', ['--email' => 'adamwathan@example.com']);

        $this->assertFileNotCreatedMsg();
        $this->assertEquals(1, User::where('email', 'adamwathan@example.com')->count());
    }

    /** @test */
    public function it_hashes_the_password_when_specified()
    {
        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

        tap(User::first(), function ($user) {
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--name' => 'Michael Dyrynda']);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        tap(['email' => 'michael@dyrynda.com.au', 'name' => 'Michael Dyrynda'], function ($credentials) {
            $this->assertTrue(User::where($credentials)->exists());
            $this->assertEquals(1, User::where($credentials)->count());
        });
    }

    /** @test */
    public function it_does_not_send_the_password_reset_email_when_the_password_is_specified()
    {
        Notification::fake();

        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

        Notification::assertNotSentTo(User::first(), ResetPassword::class);

        tap(['email' => 'michael@dyrynda.com.au'], function ($credentials) {
            $this->assertTrue(User::where($credentials)->exists());
            $this->assertEquals(1, User::where($credentials)->count());
        });
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_flagged_to_do_so()
    {
        Notification::fake();

        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--send-reset' => true]);

        Notification::assertSentTo(User::first(), ResetPassword::class);
    }

    /** @test */
    public function it_fills_additional_fields_when_specified()
    {
        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--fields' => 'admin:true']);

        $this->assertTrue(User::where([
            'email' => 'michael@dyrynda.com.au',
            'admin' => true,
        ])->exists());
    }

    /** @test */
    public function it_handles_null_field_values_correctly()
    {
        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--fields' => 'force_filled:null']);

        tap(User::first(), function ($user) {
            $this->assertNull($user->force_filled);
        });
    }

    /** @test */
    public function it_force_filles_guarded_properties_when_instructed()
    {
        Artisan::call('make:user', [
            '--email' => 'michael@dyrynda.com.au',
            '--password' => 'secret',
            '--force' => true,
            '--fields' => 'admin:false,force_filled:string field',
        ]);

        tap(User::first(), function ($user) {
            $this->assertFalse($user->admin);
            $this->assertEquals('string field', $user->force_filled);
        });
    }

    //----------------------------------------------------------------------------------
    // BULK IMPORT TESTS
    //----------------------------------------------------------------------------------
    /** @test */
    public function it_requires_a_file_with_extension()
    {
        Artisan::call('make:user', [
            '--import-file' => 'file-without-ext',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_real_file_on_disk()
    {
        Artisan::call('make:user', [
            '--import-file' => 'non-existent-file.csv',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_file_of_supported_type()
    {
        Artisan::call('make:user', [
            '--import-file' => 'file.invalid-extension',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_non_empty_csv()
    {
        $this->createFile('', 'csv');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('csv'),
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_non_empty_json()
    {
        $this->createFile('', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_well_formed_csv_file()
    {
        // trailing comma in header record
        $this->createFile("name,email,password,\n\"jon\",\"jon@email.com\",\"pass123\"", 'csv'
        );

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('csv'),
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_well_formed_json_file()
    {
        // trailing commas
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com", 
                "password" : "pass123",
            },
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_valid_email_addresses_in_json()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "bad-email-addy" 
            },
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        $this->assertFileNotCreatedMsg();
    }  

    /** @test */
    public function it_requires_valid_email_addresses_in_csv()
    {
        $this->createFile("name,email,password\n\"jon\",\"bad-email.com\",\"pass123\"", 'csv'
        );

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('csv'),
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_imports_csv_and_hashes_the_password()
    {
        $this->createFile("name,email,password\n\"jon\",\"jon@email.com\",\"pass123\"\n\"jane\",\"jane@email.com\",\"pass456\"", 'csv'
        );

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('csv'),
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertTrue(
            ! is_null($user1) && Hash::check('pass123', $user1->password)
            &&
            ! is_null($user2) && Hash::check('pass456', $user2->password)
        );
    }

    /** @test */
    public function it_imports_csv_that_excludes_password_but_creates_default_one()
    {
        $this->createFile("name,email\n\"jon\",\"jon@email.com\"\n\"jane\",\"jane@email.com\"", 'csv'
        );

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('csv'),
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertTrue(
            ! is_null($user1) && ! is_null($user1->password)
            &&
            ! is_null($user2) && ! is_null($user2->password)
        );
    }

    /** @test */
    public function it_imports_json_and_hashes_the_password()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com", 
                "password" : "pass123"
            },
            {
                "name" : "jane", 
                "email" : "jane@email.com", 
                "password" : "pass456"
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertTrue(
            ! is_null($user1) && Hash::check('pass123', $user1->password)
            &&
            ! is_null($user2) && Hash::check('pass456', $user2->password)
        );
    }

    /** @test */
    public function it_imports_json_that_excludes_password_but_creates_default_one()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com"
            },
            {
                "name" : "jane", 
                "email" : "jane@email.com"
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertTrue(
            ! is_null($user1) && ! is_null($user1->password)
            &&
            ! is_null($user2) && ! is_null($user2->password)
        );
    }

    /** @test */
    public function it_imports_file_and_fills_additional_fields_when_specified()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com",
                "admin" : true
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertEquals($user->admin, true);
        });
    }

    /** @test */
    public function it_imports_file_and_handles_null_field_values_correctly()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com",
                "password" : "sadfsadf",
                "admin" : null
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertNull($user->admin);
        });        
    }

    /** @test */
    public function it_imports_file_and_ignores_guarded_properties()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com",
                "password" : "sadfsadf",
                "force_filled" : "it works"
            }
        ]', 'json');


        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertNull($user->force_filled);
        }); 
    }

    /** @test */
    public function it_imports_file_and_force_fills_guarded_properties_when_instructed()
    {
        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com",
                "password" : "sadfsadf",
                "force_filled" : "it works"
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
            '--force' => true,
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertEquals("it works", $user->force_filled);            
        });
    }

    /** @test */
    public function it_imports_file_and_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com"
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
        });
    }

    /** @test */
    public function it_imports_file_and_does_not_send_password_reset_email_when_supplied_with_password()
    {
        Notification::fake();

        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com",
                "password" : "qfrfqfqrfq"
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
        ]);

        Notification::assertNotSentTo(User::first(), ResetPassword::class);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
        });
    }

    /** @test */
    public function it_imports_file_and_sends_the_password_reset_email_when_flagged_to_do_so()
    {
        Notification::fake();

        $this->createFile('[
            {
                "name" : "jon", 
                "email" : "jon@email.com",
                "password" : "qfrfqfqrfq"
            }
        ]', 'json');

        Artisan::call('make:user', [
            '--import-file' => $this->getFilePath('json'),
            '--send-reset' => true,
        ]);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
        });
    }

    private function createFile($contents, $type)
    {
        file_put_contents($this->getFilePath($type), $contents);
    }

    private function assertFileNotCreatedMsg()
    {
        $this->assertContains('not created', Artisan::output());
    }
}