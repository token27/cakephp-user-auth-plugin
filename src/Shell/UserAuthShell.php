<?php

namespace UserAuth\Shell;

# CAKEPHP

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use Cake\I18n\Number;
use Cake\Log\Log;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use RuntimeException;
use Throwable;

# PLUGIN 


declare(ticks=1);

class UserAuthShell extends Shell {

    /**
     * Get option parser method to parse commandline options
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser {

        $parser = parent::getOptionParser();

        $parser->addOption('username', array(
            'short' => 'u',
            'help' => 'The username who wants to use.',
            'default' => false
        ));

        $parser->addOption('password', array(
            'short' => 'p',
            'help' => 'The password who wants to use for the username.',
            'default' => false
        ));

        $parser->addOption('role', array(
            'short' => 'r',
            'help' => 'The role name who wants to use for this username.',
            'default' => 'guest'
        ));

        $parser->addOption('level', array(
            'short' => 'l',
            'help' => 'The rank role who wants to use for the role.',
            'default' => 1000
        ));

        $parser->addOption('status', array(
            'short' => 's',
            'help' => 'The status who wants to use for the username.',
            'default' => 1
        ));

        $parser->addOption('force', array(
            'short' => 'f',
            'help' => 'Force create if the params not exists (role, password).',
            'default' => 0
        ));

        return $parser;
    }

    /**
     * Overwrite shell initialize to dynamically load all Queue Related Tasks.
     *
     * @return void
     */
    public function initialize(): void {
        parent::initialize();
        $this->loadModel('UserAuth.Users');
        $this->loadModel('UserAuth.Roles');
        $this->loadModel('UserAuth.Permissions');
    }

    /**
     * Main
     *
     * @access public
     */
    public function main() {
        $this->out($this->OptionParser->help());
        return true;
    }

    /**
     * Add
     *
     * @access public
     */
    public function add() {
        $role_added = false;

        $username = isset($this->params['username']) ? $this->params['username'] : false;
        $password = isset($this->params['password']) ? $this->params['password'] : false;
        $role_name = isset($this->params['role']) && $this->params['role'] != '' ? $this->params['role'] : null;
        $role_id = null;
        $role_rank = 1000;
        $status = intval($this->params['status']);
        $force = intval($this->params['force']);

        $user_database = null;

        if (!$username) {
            $username = 'superadmin';
            $password = '1234';
        }
        if ($username == 'superadmin') {
            $role_name = 'superadmin';
            $role_rank = 1;
        }

        if (!$password) {
            $password = '1234';
        }

        if (!$role_name || $role_name === "") {
            $role_name = "basic";
        }
        $role_name = Text::slug($role_name);
        $role_database = $this->Roles->getRole($role_name);
        if ($role_database) {
            $role_id = $role_database['id'];
        } else {
            $data_role = [
                'name' => $role_name
            ];
            $role_added = $this->Roles->addRole($data_role);
            if ($role_added) {
                $role_id = $role_added->id;
            }
        }


        $this->hr();
        $this->out(__('Creating username "' . $username . '" ...'), 1, Shell::QUIET);
        $this->out(__('Force:' . $force), 1, Shell::QUIET);
        $this->hr();

        try {
            $data_user = [
                'username' => $username,
                'password' => $password,
                'status' => $status,
            ];
            if ($role_id) {
                $data_user['role_id'] = $role_id;
            }
            $user_database = $this->Users->addUser($data_user);
        } catch (\Exception $e) {
            
        }

        if ($user_database['status'] == 1) {
            $this->out(__('The username has been created !'), 1, Shell::QUIET);
            $this->out(__('Role Id: ' . $role_id), 1, Shell::QUIET);
            $this->out(__('Role Name: ' . $role_name), 1, Shell::QUIET);
            $this->out(__('Role Rank: ' . $role_rank), 1, Shell::QUIET);
            $this->hr();
            $this->out(__('Username: ' . $username), 1, Shell::QUIET);
            $this->out(__('Password: ' . $password), 1, Shell::QUIET);
            $this->out(__('Status: ' . $status), 1, Shell::QUIET);
            $this->hr();
        } else {
            $this->out(__('The username cannot be been created.'), 1, Shell::QUIET);
        }
        $this->hr();
    }

}
