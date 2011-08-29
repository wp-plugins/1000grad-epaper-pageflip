<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once('config.php');

class EPaper_db
{

    public $epTableName;
    private $WP_db;

    function __construct()
    {
        $this->WP_db = $GLOBALS['wpdb'];
        $this->epTableName = $this->WP_db->prefix . EPaper_config::$db_table_name;

        //db error loging
        #$this->WP_db->show_errors();
        #$this->WP_db->print_error();
        $this->WP_db->hide_errors();

    }

    function create_table()
    {
        if ( $this->WP_db->get_var("SHOW TABLES LIKE '$this->epTableName'") != $this->epTableName )
        {

            /*
             * The dbDelta function examines the current table structure, compares
             * it to the desired table structure, and either adds or modifies the
             * table as necessary, so it can be very handy for updates
             * (see wp-admin/upgrade-schema.php for more examples of how to use dbDelta).
             * Note that the dbDelta function is rather picky, however. For instance:
             * You have to put each field on its own line in your SQL statement.
             * You have to have two spaces between the words PRIMARY KEY and
             * the definition of your primary key.
             * You must use the key word KEY rather than its synonym INDEX and
             * you must include at least one KEY.
             *
             * http://codex.wordpress.org/Creating_Tables_with_Plugins
             */

            $sql = "CREATE TABLE `" . $this->epTableName . "` (
                `ep_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ep_name` VARCHAR(255) NOT NULL,
                `ep_title` VARCHAR(255) NOT NULL,
                `ep_link` VARCHAR(255) NOT NULL,
                `ep_view_as` SET('Link','Animiertes GIF', 'Grafik') NOT NULL,
                `ep_last_change` TIMESTAMP NOT NULL,
                `ep_text_link` TEXT,
                PRIMARY  KEY  (`ep_id`),
                UNIQUE (`ep_id`))";

            // fire sql
            dbDelta($sql);
        }

    }

    function getAll()
    {
        $sql_get_allePaper = "SELECT * FROM " . $this->epTableName;

        return $this->WP_db->get_results($sql_get_allePaper, ARRAY_A);

    }

    function getEpaperAfterTitle( $title )
    {
        $sql = "SELECT * FROM " . $this->epTableName .
            " WHERE ep_title='" . $title . "' LIMIT 1";

        return $this->WP_db->get_results($sql, ARRAY_A);

    }

    function getViewOptions()
    {
        $sql = "SHOW COLUMNS FROM " . $this->epTableName . " LIKE 'ep_view_as'";

        $row = $this->WP_db->get_results($sql, ARRAY_N);

        $setEl = str_replace(array('"', 'set(', ')', '\''), "", $row[0][1]);

        return explode(",", $setEl);

    }

    function getAllEpaperTitle()
    {
        $sql = "SELECT ep_title FROM " . $this->epTableName;

        return $this->WP_db->get_results($sql, ARRAY_N);

    }

    function deleteEPaper( $title )
    {
        $sql = ("
            DELETE FROM " . $this->epTableName . " WHERE ep_title = '" . $title . "'
            ");

        return $this->WP_db->query($sql);

    }

    function updateEPaper( $epaper, $oldTitle )
    {
        $res = $this->WP_db->update(
                $this->epTableName, array(
                'ep_name' => $epaper['ep_name'],
                'ep_title' => $epaper['ep_title'],
                'ep_link' => $epaper['ep_link'],
                'ep_view_as' => $epaper['ep_view_as'],
                'ep_text_link' => ($epaper['ep_text_link'] == 0) ? $epaper['ep_text_link'] : NULL
                ), array('ep_title' => $oldTitle));
        return $res;

    }

    function insertEPaper( $epaper )
    {
        $res = $this->WP_db->insert($this->epTableName, array(
                'ep_name' => $epaper['ep_name'],
                'ep_title' => $epaper['ep_title'],
                'ep_link' => $epaper['ep_link'],
                'ep_view_as' => $epaper['ep_view_as'],
                'ep_text_link' => ($epaper['ep_text_link'] == 0) ? $epaper['ep_text_link'] : NULL
            ));
        return $res;

    }

    function getEpaperMetaBox()
    {
        $sql = "SELECT ep_name, ep_view_as, ep_title, ep_text_link FROM " . $this->epTableName;

        return $this->WP_db->get_results($sql, ARRAY_A);

    }

}

