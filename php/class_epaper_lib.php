<?php

require_once('config.php');

class EPaper_lib {

  public $viewOptionsAr = array();
  public $EP_db;
  public $pluginSiteUrl;
  public $websiteUrl;
  public $rgUrlPattern;
  public $updateTitle;
  public $absPathEpaperDir;

  function __construct() {
    $EP_db = new EPaper_db();

    $this->EP_db = new EPaper_db();

    $this->websiteUrl = get_bloginfo('wpurl') . "/wp-admin/admin.php?";

    $this->pluginSiteUrl = $this->websiteUrl . "page=epaperManager&esid=";

    //$this->absPathEpaperDir = get_bloginfo( 'wpurl' ) . "/wp-content/plugins/1000grad-epaper-wp-plugin/";
    $this->absPathEpaperDir = get_bloginfo('wpurl') . "/wp-content/plugins/1000grad-epaper-pageflip/";

    // pattern from http://phpcentral.com/208-url-validation-in-php.html
    $this->rgUrlPattern = "|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i";

    $this->viewOptionsAr = $this->EP_db->getViewOptions();

    ( isset($_POST['ep_update_old_title']) ) ? $this->updateTitle = $_POST['ep_update_old_title'] : $this->updateTitle = "";
  }

  /*
   * GET "ESID" URL PARAM AND FORWARD TO RESPECTIVE PAGE
   */

  function init() {
    switch (@$_GET['esid']) {
      case "insert_epaper":
        $this->renderCreatePage();
        break;

      case "edit_epaper" :
        $this->renderEditPage();
        break;

      case "delete_epaper" :
        $this->renderDeletePage();
        break;

      default:
        $this->printOverview();
    }
  }

  /*
   * enqueue script and styles only on pages that contain a "[epaper]" shortcode
   */

  function conditionally_add_scripts_and_styles($posts) {
    if (empty($posts))
      return $posts;

    $shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued

    foreach ($posts as $post) {
      if (stripos($post->post_content, 'epaper')) {
        $shortcode_found = true; // bingo!
        break;
      }
    }

    if ($shortcode_found) {
      // enqueue here
      wp_enqueue_script('js_ep_script', $this->absPathEpaperDir .
        'js/initShadowbox.js', array('js_shadowbox'));

      wp_enqueue_script('js_shadowbox', $this->absPathEpaperDir .
        'shadowbox/shadowbox.js');

      wp_enqueue_style('style_shadowbox', $this->absPathEpaperDir .
        'shadowbox/shadowbox.css');

      wp_enqueue_script('js_swfaddress', $this->absPathEpaperDir .
        'js/swfaddress.js');

      wp_enqueue_script('js_swfobject', $this->absPathEpaperDir .
        'js/swfobject.js');
    }

    return $posts;
  }

  /*
   *  enqueue shadowbox js, only for epaper plugin page
   */

  function enqueue_js_admin() {
    wp_enqueue_script('js_ep_script', $this->absPathEpaperDir .
      'js/ep_main.js', array('js_shadowbox'));

    wp_enqueue_script('js_shadowbox', $this->absPathEpaperDir .
      'shadowbox/shadowbox.js');

    wp_enqueue_script('js_swfaddress', $this->absPathEpaperDir .
      'js/swfaddress.js');

    wp_enqueue_script('js_swfobject', $this->absPathEpaperDir .
      'js/swfobject.js');

    wp_enqueue_script('js_jquery_rotate', $this->absPathEpaperDir .
      'js/jQueryRotateCompressed.2.1.js', array('jquery'));
  }

  /*
   *  enqueue shadowbox js, only for edit and post admin page
   */

  function enqueue_js_admin_edit_post($hook) {
    if ($hook == 'post.php' || $hook == 'post-new.php') {
      wp_enqueue_script('js_ep_post_editor', $this->absPathEpaperDir .
        'js/ep_post_editor.js', array('jquery'));
    }
  }

  /*
   *  enqueue shadowbox css, only for epaper plugin page
   */

  function enqueue_css_admin() {
    wp_enqueue_style('style_shadowbox', $this->absPathEpaperDir .
      'shadowbox/shadowbox.css');
  }

  /*
   *  enqueue css admin menu styles to wordpress
   */

  function enqueue_admin_css() {
    wp_enqueue_style('style_admin_epaper_manager', $this->absPathEpaperDir .
      'css/epaper_main.css');
  }

  /*
   * PRINT PAGES
   */

  function printAdminPage($page_title, $render_add_btn = false) {
    echo ("
            <div id=\"ep_main\">
            <div id='ep_navi'> <span id=\"ep_header_font\"> " . $page_title . "</span><br /><br />
                ");

    if ($render_add_btn) {
      echo "<a class=\"button-secondary action\" href=\"" . $this->pluginSiteUrl . "insert_epaper\">ePaper einfügen</a>";
    }

    echo "</div>";
  }

  function printOverview() {
    // get all epapers from db
    $all_epapers = $this->EP_db->getAll();

    // render page header and epaper overview
    $this->printAdminPage("ePaper Manager", true);

    // if no epaper exists, render message "add one"
    if (0 == count($all_epapers)) {
      echo "Sie haben noch keine ePaper eingefügt...";
      echo "</div>"; // close div from printAdminPage function
      return;
    }

    // check for feedback messages from insert, update or delete events
    if (isset($_GET['db_insert']) && $_GET['db_insert'] == "success") {
      echo ("<div class=\"updated below-h2\" id=\"ep_success_box\"> Das ePaper wurde erfolgreich
                    in der Datenbank gespeichert</div>");
    }

    if (isset($_GET['db_insert']) && $_GET['db_insert'] == "error") {
      echo ("<div class=\"updated below-h2\" id=\"ep_error_box\"> Das ePaper konnte nicht erfolgreich
                    in der Datenbank gespeichert</div>");
    }
    if (isset($_GET['db_update']) && $_GET['db_update'] == "success") {
      echo ("<div class=\"updated below-h2\" id=\"ep_success_box\"> Das Update des ePaper wurde erfolgreich
                    in der Datenbank gespeichert</div>");
    }

    if (isset($_GET['db_update']) && $_GET['db_update'] == "error") {
      echo ("<div class=\"updated below-h2\" id=\"ep_error_box\"> Das Update des ePaper konnte nicht erfolgreich
                    in der Datenbank gespeichert</div>");
    }
    if (isset($_GET['db_delete']) && $_GET['db_delete'] == "success") {
      echo ("<div class=\"updated below-h2\" id=\"ep_success_box\"> Das ePaper wurde erfolgreich
                    aus der Datenbank gelöscht</div>");
    }

    if (isset($_GET['db_delete']) && $_GET['db_delete'] == "error") {
      echo ("<div class=\"updated below-h2\" id=\"ep_error_box\"> Das ePaper konnte nicht
                    aus der Datenbank gelöscht werden</div>");
    }

    // print all epaper from db to overview
    #echo "<div id=\"ep_magazin_container\">";
    foreach ($all_epapers as $epaper) {
      echo ("
            <div class=\"ep_magazin\">
                <div class=\"ep_magazin_cover\">
                    <img width=\"140\" height=\"200\" class=\"ep_magazin_cover_img ep_magazin_cover_3\" src=\"" . $this->absPathEpaperDir . "img/dummy_page_120x160.jpg\" alt=\"\" />
                    <img width=\"140\" height=\"200\" class=\"ep_magazin_cover_img ep_magazin_cover_2\" src=\"" . $this->absPathEpaperDir . "img/dummy_page_120x160.jpg\" alt=\"\" />
                    <img width=\"140\" height=\"200\" class=\"ep_magazin_cover_img ep_magazin_cover_1\" src=\"" . $epaper['ep_link'] . "epaper/preview.jpg\" alt=\"\" />
                </div>
                
                <div class=\"ep_magazion_info\"><table width=\"195px\">
                    <tr>
                        <td style=\"padding-bottom:0px;\" colspan=\"2\" class=\"ep_magazin_bold\">Name</td>
                    </tr>
                     <tr>
                        <td style=\"font-size:12px;\" colspan=\"2\">" . $epaper['ep_name'] . "</td>
                    </tr>
                    <tr>
                        <td class=\"ep_magazin_bold\">Anzeigeart: </td>
                        <td>" . $epaper['ep_view_as'] . "</td>
                    </tr>
                    <tr>
                        <td class=\"ep_magazin_bold\">letzes Update: </td>
                        <td>" . date('d.m.Y G:i', strtotime($epaper['ep_last_change'])) . " Uhr</td>
                    </tr>
                    <tr height=\"35\">
                        <td colspan=\"2\"> <a class=\"button-secondary ep_magazin_preview_btn\" href=\"javascript:;\" onclick=\"Shadowbox.open({content:'" . $epaper['ep_link'] . "index.html?closeAction=parent.Shadowbox.close',player:'iframe',title:'" . $epaper['ep_name'] . "'});\">Vorschau</a> </td>
                        
                    </tr>
                    <tr>
                        <td> <a class=\"button-secondary\" href=\"" . $this->pluginSiteUrl . "edit_epaper&ep_title=" . $epaper['ep_title'] . "\">bearbeiten</a> </td>
                        <td> <a class=\"button-secondary ep_delete_link\" href=\"" . $this->pluginSiteUrl . "delete_epaper&ep_title=" . $epaper['ep_title'] . "\">löschen</a> </td>
                    </tr>                  
                </table></div>
            </div>
            ");
    }

    echo "</div>"; // close div from printAdminPage function
  }

  function renderEditPage() {
    // if no epaper was selected throw error message
    if (!isset($_GET['ep_title']) || ( isset($_GET['ep_title']) && strlen($_GET['ep_title']) == 0 )) {
      echo "<div style=\"margin-left: 15px;margin-top:30px;\">Sie haben kein ePaper ausgewählt...</div>";
      return;
    }

    $this->printAdminPage("ePaper bearbeiten");
    $this->renderCreatePage($this->EP_db->getEpaperAfterTitle($_GET['ep_title']));
    echo "</div>";
  }

  function renderDeletePage() {
    // if no epaper was selected to delete throw error message
    if (!isset($_GET['ep_title']) || ( isset($_GET['ep_title']) && strlen($_GET['ep_title']) == 0 )) {
      echo "<div style=\"margin-left: 15px;margin-top:30px;\">Sie haben kein ePaper zum Löschen ausgewählt...</div>";
      return;
    }

    // get all epaper from db, iterate through them
    // delete epapers with same name as selected
    foreach ($this->EP_db->getAllEpaperTitle() as $title) {
      if ($_GET['ep_title'] == $title[0]) {
        ( true == $this->EP_db->deleteEPaper($_GET['ep_title']) ) ?
            $feedback_delete = "success" : $feedback_delete = "error";

        echo '<script type="text/javascript">window.location.href = "' .
        $this->pluginSiteUrl . '&db_delete=' . $feedback_delete . '";</script>';
      }
    }
  }

  function renderCreatePage($editEpaperInfo) {
    // check if formular was sent
    if (isset($_POST['insert_epaper_status']) && $_POST['insert_epaper_status'] == "sent") {
      // run through validation
      // on no error, prepare epaper information for db import
      $res_renderValidation = $this->renderValidation();

      if ($res_renderValidation[0] == 0) {
        // add missing slash at the end of epaper url
        if (substr($_POST['ep_link'], strlen($_POST['ep_link']) - 1, 1) != "/") {
          $_POST['ep_link'] .= "/";
        }

        // gather all information for db import
        $epaperInfo = array(
          "ep_name" => $_POST['ep_name'],
          "ep_title" => strtolower($this->prepare_ep_title_for_db($_POST['ep_name'])),
          "ep_link" => $_POST['ep_link'],
          "ep_view_as" => $_POST['ep_view_as'],
          "ep_text_link" => $_POST['ep_text_link'],
          // deprecated info
          "ep_author_id" => $_POST['ep_author_id'],
          "ep_scrollbars" => $_POST['ep_scrollbars'],
          "ep_width" => $_POST['ep_width'],
          "ep_height" => $_POST['ep_height'],
          "ep_menu" => $_POST['ep_menu']
        );

        // check if epaper is new or should be updated
        switch (@$_GET['esid']) {
          case "insert_epaper":
            $res = $this->EP_db->insertEPaper($epaperInfo);
            $db_action = "db_insert";
            break;

          case "edit_epaper":
            $res = $this->EP_db->updateEPaper($epaperInfo, $this->updateTitle);
            $db_action = "db_update";
            break;
        }

        // check if db action was successfull
        ( true == $res ) ? $feedback_db_handle = "success" : $feedback_db_handle = "error";

        //forward to epaper overview site and give feedback about db import/update
        echo '<script type="text/javascript">window.location.href = "' . $this->pluginSiteUrl . '&' . $db_action . '=' . $feedback_db_handle . '";</script>';
        #var_dump($res);
      }
    }

    // check if an epaper is newly inserted or updated
    // render pages accordingly
    switch ($_GET['esid']) {
      case 'insert_epaper':
        $this->printAdminPage("ePaper einfügen");
        echo $res_renderValidation[1];
        //render form with epaper information alreay wrote to form fields
        $this->renderForm($_POST);
        echo "</div>";
        break;

      case 'edit_epaper':
        // render form with epaper information from db
        echo $res_renderValidation[1];
        $this->renderForm($editEpaperInfo[0]);
        break;
    }
  }

//end function renderCreatePage
  // convert epaper name to title
  // (strip special charactes, replace whitespaces by "_")
  function prepare_ep_title_for_db($ep_name) {
    $ep_name_replace_special = array("/", ",", ";", "!", "$", "%", "&", "(", ")", "=", "\\", "´", "'", "#", "*", "+", "*", "~", "[", "]", ":", ".", "<", ">");
    $ep_name_replace_german = array("ä", "ü", "ö", "ß");
    $ep_name_replace_german_by = array("ae", "ue", "oe", "ss");

    $ep_title = str_replace($ep_name_replace_german, $ep_name_replace_german_by, $ep_name);
    $ep_title = str_replace($ep_name_replace, "-", $ep_title);
    $ep_title = str_replace(" ", "_", $ep_title);

    return $ep_title;
  }

  function renderForm($formData) {
    // print formular to create an epaper
    echo("<div id=\"ep_form\">
            
            <span id=\"ep_absPathEpaperDir\" style=\"display:none;\">" . $this->absPathEpaperDir . "</span>
            <span id=\"ep_pluginSiteUrl\" style=\"display:none;\">" . $this->pluginSiteUrl . "</span>
            
            <form method=\"post\" action=\"" . $this->websiteUrl . $_SERVER['QUERY_STRING'] . "\">
                <input type=\"submit\" value=\"Speichern\" />
                <input type=\"button\" id=\"ep_reset\" value=\"Abbrechen\" />
                <input type=\"hidden\" name=\"insert_epaper_status\" value=\"sent\" />
                <input type=\"hidden\" name=\"ep_update_old_title\" value=\"" . @$formData['ep_title'] . "\" />
                <br /><br />
                
                <table width=\"600px\">
                    <tr>
                        <td>Name: </td>
                        <td> <input style=\"width:300px;\" name=\"ep_name\" type=\"text\" size=\"30\" maxlength=\"255\" value=\"" .
    $this->writeFormValue('ep_name', @$formData['ep_name']) . "\"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class=\"ep_meta_info\">Der Name des ePapers. Aus diesem wird der automatisch<br />der Shortcode für den Editor generiert.</span></td>
                    </tr>
                    <tr>
                        <td>Link zu ePaper: </td>
                        <td> <input style=\"width:360px;\" id=\"ep_link\" name=\"ep_link\" type=\"text\" size=\"50\" maxlength=\"255\" value=\"" .
    $this->writeFormValue('ep_link', @$formData['ep_link']) . "\">
                            <img id=\"ep_loader\" src=\"" . $this->absPathEpaperDir . "img/ajax-loader.gif\" alt=\"loader grafik\" />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class=\"ep_meta_info\">Die URL zum ePaper<br />Beispiel:  http://www.meinedomain,com/pfad/zum/epaper</span></td>
                    </tr>
                    <tr>
                        <td>&nbsp; </td>
                        <td>
                            <input " . $this->checkSelRadioBtn('Link', @$formData['ep_view_as']) . " id=\"ep_radio_link_btn\" type=\"radio\" name=\"ep_view_as\" value=\"Link\" /> Link <br />
                            <input " . $this->checkSelRadioBtn('Animiertes GIF', @$formData['ep_view_as']) . " id=\"ep_radio_gif_btn\" type=\"radio\" name=\"ep_view_as\" value=\"Animiertes GIF\" /> Animiertes GIF<br />
                            <input " . $this->checkSelRadioBtn('Grafik', @$formData['ep_view_as']) . " id=\"ep_radio_grafik_btn\" type=\"radio\" name=\"ep_view_as\" value=\"Grafik\" /> Grafik<br />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class=\"ep_meta_info\">Legt fest, wie das ePaper angezeigt werden soll. Durch Klick<br />öffnet sich das ePaper in der Shadowbox.</span></td>
                    </tr>  
                    <tr class=\"ep_text_link_row\">
                        <td>Text für Link</td>
                        <td> <input style=\"width:300px;\" name=\"ep_text_link\" type=\"text\" size=\"50\" maxlength=\"500\" value=\"" .
    $this->writeFormValue('ep_text_link', @$formData['ep_text_link']) . "\"> </td>
                    </tr>
                    <tr class=\"ep_text_link\">
                        <td></td>
                        <td>Text für den Link</td>
                    </tr>
                    <tr>                
                </table>
            </form></div>
            <div id=\"ep_preview_img\">
                <img class=\"ep_preview_img_src ep_magazin_cover_img ep_magazin_cover_1\" src=\"\" alt=\"preview epaper\" />
            </div>
            ");
  }

  // print error box if validation was incomplete
  // return number of errors as integer
  function renderValidation() {
    $val_res = $this->validateForm();
    $_html = "";

    if (count($val_res) > 0) {
      $_html = "<div id=\"ep_error_box_no_hide\"><h4>Es sind folgende Fehler aufgetreten:</h4><ul>";

      foreach ($val_res as $item) {
        $_html .= "<li>" . $item . "</li>";
      }
      $_html .= "</ul><br /></div>";
    }
    return array((int) count($val_res), $_html);
  }

  // check user input
  function validateForm() {
    // create array to add all errors
    $error = array();

    if (isset($_POST['insert_epaper_status']) && $_POST['insert_epaper_status'] == 'sent') {
      // CHECK EPAPER NAME
      if (!isset($_POST['ep_name']) || strlen($_POST['ep_name']) == 0) {
        $error[] = "Der Name für das ePaper fehlt";
      }

      if (isset($_POST['ep_name']) && strlen($_POST['ep_name']) > 100) {
        $error[] = "Der Name ist zu lange (max. 100 Zeichen)";
      }

      foreach ($this->EP_db->getAllEpaperTitle() as $title) {
        if ($this->prepare_ep_title_for_db($_POST['ep_name']) == $title[0]
          && $this->prepare_ep_title_for_db($_POST['ep_name']) != $this->updateTitle) {
          $error[] = "Es gibt bereits ein ePaper mit diesem Namen";
        }
      }

      // CHECK EPAPER URL
      if (substr($_POST['ep_link'], strlen($_POST['ep_link']) - 1, 1) != "/") {
        $_POST['ep_link'] .= "/";
      }

      if (!preg_match($this->rgUrlPattern, $_POST['ep_link'])) {
        $error[] = "Die ePaper URL ist fehlerhaft<br /> http://www.example.com";
      }

      if (false === @file_get_contents($_POST['ep_link'] . "epaper/preview.jpg")) {
        $error[] = "Der angegebene ePaper Link existiert nicht";
      }

      if (strlen($_POST['ep_link']) == 1) {
        $_POST['ep_link'] = "";
      }

      // CHECK VIEWING OPTIONS
      if (!isset($_POST['ep_view_as']) && !in_array($_POST['ep_view_as'], $this->viewOptionsAr)) {
        $error[] = "Sie haben eine ungültige Anzeigeoption ausgewählt";
      }

      if (isset($_POST['ep_view_as'])) {
        if ($_POST['ep_view_as'] == "Link") {
          if (!isset($_POST['ep_text_link']) || strlen($_POST['ep_text_link']) == 0) {
            $error[] = "Sie haben keinen Text für den Link eingegeben";
          }
        }

        if ($_POST['ep_view_as'] == "Animiertes GIF" && false === @file_get_contents($_POST['ep_link'] . "epaper/epaper-ani.gif")) {
          $error[] = "Für Ihr ePaper gibt es keine Animation Preview. Bitte wählen sie eine andere Anzeigeart";
        }
      }
    }
    return $error;
  }

  function writeFormValue($fieldName, $editValue = "") {
    if (isset($_POST[$fieldName]) && strlen($_POST[$fieldName]) > 0) {
      return $_POST[$fieldName];
    }
    return $editValue;
  }

  function checkSelRadioBtn($fieldName, $formValue) {
    if ($fieldName == $_POST['ep_view_as'] || $fieldName == $formValue) {
      return "checked=\"checked\"";
    }
  }

  /*
   * META BOX FOR POST EDITING
   *
   */

  function addEpaperMetaBox() {
    add_meta_box(
      'ep_meta_box_overview', __('ePaper Überblick', 'myplugin_textdomain'), array(&$this, 'renderMetaBox'), 'post', 'side', 'high'
    );
  }

  function renderMetaBox() {

    echo ("<div id=\"ep_metabox_overview_navi\">
                <div id=\"ep_metabox_overview_epaper\">Übersicht</div>
                <div id=\"ep_metabox_overview_help\">Hilfe</div>
            </div>
            <div id=\"ep_metabox_overview_content\">
            ");


    $ep_count = 1;
    $ep_count_db = sizeof($this->EP_db->getAll());

    if ($ep_count_db == 0) {
      echo "<div class=\"ep_meta_single\">";
      echo "Es wurden noch keine ePaper eingef&uuml;gt";
      echo "</div>";
    } else {
      foreach ($this->EP_db->getAll() as $epaper) {

        if ($ep_count == $ep_count_db) {
          echo "<div class=\"ep_meta_single ep_meta_single_last\">";
        } else {
          echo "<div class=\"ep_meta_single\">";
        }

        echo "<img class=\"add_epaper_button\" src=\"" . $this->absPathEpaperDir . "img/button_add_quick.gif\" alt=\"add_epaper_to_editor\" />";
        echo "Name: " . $epaper['ep_name'] . "<br />";
        echo "<span style=\"font-size:10px; color:#aaa;\">Titel: </span><span class=\"ep_meta_info_title\">" . $epaper['ep_title'] . "</span> ";

        if (strlen($epaper['ep_text_link']) != 0 && $epaper['ep_view_as'] == "Link") {
          echo "<span class=\"ep_meta_info\">, Linktext:</span> <span class=\"ep_text_link\">\"" . $epaper['ep_text_link'] . "\"</span> ";
        }
        echo "<span class=\"ep_meta_info_view\">, " . $epaper['ep_view_as'] . "</span>";
        echo "</div>";

        $ep_count++;
      }
    }



    echo "</div><div id=\"ep_metabox_overview_help_content\">
                <p>So fügen Sie das ePaper manuell hinzu</p>
                <p>[epaper title='TITEL_EPAPER']</p>
                <p>Folgende Elemente sind erlaubt:</p>
                <ul>
                    <li> <img class=\"add_link_tag\" src=\"" . $this->absPathEpaperDir . "img/button_add_quick.gif\" alt=\"add_link_text_to_epaper\" /> link_text <br /> <span class=\"ep_meta_info\">Hiermit k&ouml;nnen Sie den voreingestellten Linktext &uuml;berschreiben.</span></li>
                    <li> <img class=\"add_css_class\" src=\"" . $this->absPathEpaperDir . "img/button_add_quick.gif\" alt=\"add_css_class_to_epaper\" /> css_class <br /> <span class=\"ep_meta_info\">Hiermit k&ouml;nnen Sie eine CSS-Klasse für den Link festlegen.</span></li>
                </ul>
            </div>";
  }

  /*
   * 
   * SHORT CODE API
   * 
   */

  function epaperShortcode($atts) {
    extract(shortcode_atts(array(
        'title' => "",
        'view' => "",
        'link_text' => "",
        'css_class' => ""
        ), $atts));

    if (strlen($title) != 0) {
      $res = $this->EP_db->getEpaperAfterTitle($title);
      $res = $res[0];

      if (strlen($link_text) != "") {
        $res['ep_text_link'] = $link_text;
      }

      $epaperHTML = "";

      switch ($res['ep_view_as']) {
        case 'Link':
          $epaperHTML = "<a class=\"" .
            $css_class . "\" onclick=\"Shadowbox.open({content:'" .
            $res['ep_link'] . "index.html?closeAction=parent.Shadowbox.close',player:'iframe',title:'" .
            $res['ep_name'] . "'});\" href=\"javascript:;\">" .
            $res['ep_text_link'] . "</a>";
          break;

        case 'Animiertes GIF':
          $epaperHTML = "<a class=\"" .
            $css_class . "\" onclick=\"Shadowbox.open({content:'" .
            $res['ep_link'] . "index.html?closeAction=parent.Shadowbox.close',player:'iframe',title:'" .
            $res['ep_name'] . "'});\" href=\"javascript:;\"> <img src=\"" .
            $res['ep_link'] . "epaper/epaper-ani.gif\" alt=\"epaper preview gif\" border=\"0\" /> </a>";
          break;

        case 'Grafik':
          $epaperHTML = "<a class=\"" .
            $css_class . "\" onclick=\"Shadowbox.open({content:'" .
            $res['ep_link'] . "index.html?closeAction=parent.Shadowbox.close',player:'iframe',title:'" .
            $res['ep_name'] . "'});\" href=\"javascript:;\"> <img src=\"" .
            $res['ep_link'] . "epaper/preview.jpg\" alt=\"epaper cover\" border=\"0\" /> </a>";
          break;
      }
    }
    return $epaperHTML;
  }

}

//end class EPaper_lib