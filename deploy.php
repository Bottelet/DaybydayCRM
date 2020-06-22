<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'my_project');

// Project repository
set('repository', 'git@github.com:Bottelet/flarepoint-crm.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Hosts

host('crm.augetecnologia.com')
    ->user('augetecnologia')
    ->set('http_user','augetecnologia')
    ->port(2222)
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->set('deploy_path', '~/flarepoint-crm');    
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');