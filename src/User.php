<?php declare(strict_types=1);
/**
 * PHPCore - User
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * User Class
 */
final class User
{
    use Core;

    /**
     * Constant flags
     *
     * @const int
     */
    const HAS_ANY_ROLE  = 1;    // Has Access check true on any match 
    const HAS_ALL_ROLES = 2;    // Has Access check true if ALL match
    const TEMP_ASSIGN_ROLE = 4; // Assign the role only for this session

    // ---------------------------------------------------------------------

    /**
     * User ID
     *
     * @var integer
     */
    public readonly integer $UserId;

    // ---------------------------------------------------------------------

    /**
     * User data
     *
     * @var array
     */
    private array $Data = [];

    /**
     * User roles
     *
     * @var array
     */
    private array $Roles = [];

    // ---------------------------------------------------------------------

    // TODO: Document
    public static function getUser(int $user_id, array $where = []): null|object
    {
        $where['UserId'] = intVal($user_id);
        $data = database()->deleteRecord($this->Config['read_table'], $where);
        if ( ! empty($data)) {
            return self($data);
        } else {
            return null;
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Contructor
     *
     * @param int|array $data User data
     */
    public function __construct(?array $data = null): void
    {
        $this->Config = core_ini_get_all('User');
        $this->Roles = $this->Config['roles.default_guest'];

        if (empty($data) || empty($data['UserId'])) {
            return;
        }

        $this->Roles = $this->Config['roles.default_user'];
        $this->Data = $data;

        $this->UserId = intval($data['UserId']);
    }

    /**
     * Add role to user
     *
     * This method adds roles to a current user.
     *
     * @param string|array $roles Roles or array of roles to be added
     * @param integer $flags Bitwise flags for this method
     * @flag User::TEMP_ASSIGN_ROLE Assign the role only for this session
     * @return void
     */
    public function addRole(string|array $roles, int $flags = 0): void
    {
        $this->loadRoles();

        $roles = is_string($roles) ? [$roles] : $roles;

        $this->Roles = array_unique(array_merge($this->Roles, $roles));

        if ($flags & self::TEMP_ASSIGN_ROLE) {
            $cur_sess_roles = session_get_metadata('UserTempRoles');
            $new_sess_roles = array_unique(array_merge($cur_sess_roles, $roles));
            session_set_metadata('UserTempRoles', $new_sess_roles);
        } else {
            $base_record = [ 'UserId' => $this->UserId ];
            if (isset($this->Config['roles.by_field'])) {
                $by_field = $this->Config['roles.by_field'];
                $base_record[$by_field] = session_get_metadata($by_field);
            }
            
            $records = [];
            foreach ($roles as $role) {
                $records[] = array_merge($base_record, [ 'Role' => $role ]);
            }

            database()->createRecords($this->Config['roles.write_table'], $records);
        }
    }

    /**
     * Check if user has role
     *
     * This method checks if user has access via checking if in the
     * ``Roles`` array in the sessions metadata. By default is will match
     * **ANY** of the roles, You may pass the optional flag 
     * ``User::HAS_ALL_ROLES`` if you want to preform a match on all roles
     * for the check to be true.
     *
     * @param string|array $roles Role string or array of roles to check access for
     * @param integer $flags Bitwise flags for this method
     * @flag User::HAS_ANY_ROLE Has Access check true on ANY match 
     * @flag User::HAS_ALL_ROLES Has Access check true if ALL match
     * @return boolean If user has role
     */
    public function hasRole(string|array $roles, int $flags = 0): bool
    {
        $this->loadRoles();

        $test_roles = is_string($roles) ? [$roles] : $roles;
        $curr_roles = $this->Roles;

        if ( ! ($flags & self::HAS_ANY_ROLE) and ! ($flags & self::HAS_ALL_ROLES)) {
            $flags += self::HAS_ANY_ROLE;
        }

        if ($flags & self::HAS_ALL_ROLES) {
            return array_every($test_roles, function($role) use($curr_roles) {
                return in_array($role, $curr_roles);
            });
        }

        if ($flags & self::HAS_ANY_ROLE) {
            return array_some($test_roles, function($role) use($curr_roles) {
                return in_array($role, $curr_roles);
            });
        }
    }

    /**
     * Remove role from user
     *
     * This method removes session access via removing from ``Roles``
     * array in the sessions metadata.
     *
     * @param string|array $roles Role or array of roles to be revoked
     * @return void
     */
    public function removeRole(string|array $roles): void
    {
        $this->loadRoles();

        $roles = is_string($roles) ? [$roles] : $roles;

        $cur_roles = $this->Roles;
        $this->Roles = array_filter($this->Roles, function($role) use(&$roles) {
            return ! in_array($role, $roles);
        });

        $cur_sess_roles = session_get_metadata('UserTempRoles');
        $new_sess_roles = array_filter($cur_sess_roles, function($role) use(&$roles) {
            return ! in_array($role, $roles);
        });
        if ( ! empty(array_diff($cur_sess_roles, $new_sess_roles))) {
            session_set_metadata('UserTempRoles', $new_sess_roles);
        }

        $cur_perm_roles = array_filter($cur_roles, function($role) use(&$cur_sess_roles) {
            return ! in_array($role, $cur_sess_roles);
        });
        $new_perm_roles = array_filter($cur_roles, function($role) use(&$roles) {
            return ! in_array($role, $roles);
        });
        if ( ! empty(array_diff($cur_perm_roles, $new_perm_roles))) {
            $where = [
                'UserId' => $this->UserId,
                'Role IN' => $roles,
            ];
            if (isset($this->Config['roles.by_field'])) {
                $by_field = $this->Config['roles.by_field'];
                $where[$by_field] = session_get_metadata($by_field);
            }

            database()->deleteRecords($this->Config['roles.write_table'], $where);
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Load roles
     *
     * This method is used to load the roles for a user from both the database
     * and the temporary roles assinged in the session. This method may be
     * called mutiple times, however the loading is ONLY performed once.
     *
     * @return void
     */
    protected static function loadRoles(): void
    {
        static $loaded;

        if ( ! isset($loaded) && ! empty($this->UserId)) {
            $sess_roles = session_get_metadata('UserTempRoles');
            $where = [ 'UserId' => $this->UserId ];
            if (isset($this->Config['roles.by_field'])) {
                $by_field = $this->Config['roles.by_field'];
                $where[$by_field] = session_get_metadata($by_field);
            }
            $perm_roles = database()->getRecords(
                $this->Config['roles.read_table'],
                $where,
                Database::RETURN_FLATTEN_ARRAY
            );
            $this->Roles = array_unique(array_merge($this->Roles, $sess_roles, $perm_roles));
            $loaded = true;
        }
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
