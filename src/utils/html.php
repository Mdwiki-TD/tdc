<?php

declare(strict_types=1);

/**
 * HTML Generation Utilities Module
 * 
 * Provides helper functions for generating consistent HTML components
 * throughout the Translation Dashboard application. These functions
 * ensure proper escaping, consistent styling, and reduce code duplication.
 * 
 * Features:
 * - Alert and notification components
 * - Modal dialogs
 * - Form input groups
 * - Navigation links (MDWiki, Wikipedia, translation URLs)
 * - Dropdown menus and data lists
 * - Card and panel components
 * 
 * Security:
 * - All output functions use htmlspecialchars() for XSS prevention
 * - URLs are properly encoded with rawurlencode()
 * - HTML attributes are escaped
 * 
 * Usage Example:
 * ```php
 * use function Utils\Html\make_mdwiki_title;
 * use function Utils\Html\make_target_url;
 * use function Utils\Html\div_alert;
 * 
 * // Generate MDWiki link
 * echo make_mdwiki_title('COVID-19');
 * 
 * // Generate Wikipedia link
 * echo make_target_url('مرض_فيروس_كورونا_2019', 'ar', 'COVID-19');
 * 
 * // Display success message
 * echo div_alert(['Settings saved successfully'], 'success');
 * ```
 * 
 * @package    Utils
 * @subpackage Html
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Utils\Html;

use Tables\SqlTables\TablesSql;

/**
 * Generate a banner alert with danger styling
 * 
 * @param string $text The alert message
 * 
 * @return string HTML for the alert banner
 */
function banner_alert(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return <<<HTML
    <div class='container'>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {$escaped}
        </div>
    </div>
    HTML;
}

/**
 * Generate a login card component
 * 
 * @return string HTML for the login card
 */
function login_card(): string
{
    return <<<HTML
    <div class='card' style='font-weight: bold;'>
        <div class='card-body'>
            <div class='row'>
                <div class='col-md-10'>
                    <a role='button' class='btn btn-outline-primary' onclick='login()'>
                        <i class='fas fa-sign-in-alt fa-sm fa-fw mr-1'></i><span class='navtitles'>Login</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    HTML;
}

/**
 * Generate a Bootstrap modal dialog
 * 
 * @param string $label   Modal title
 * @param string $text    Modal body content
 * @param string $id      Modal element ID
 * @param string $button  Additional button HTML (optional)
 * 
 * @return string HTML for the modal
 */
function make_modal_fade(string $label, string $text, string $id, string $button = ''): string
{
    $randomId = 'modalLabel' . random_int(1000, 9999);
    $escaped_label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $escaped_text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
        <div class="modal fade" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="{$randomId}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="{$randomId}">{$escaped_label}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">{$escaped_text}</div>
                    <div class="modal-footer">
                        {$button}
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    HTML;
}

/**
 * Generate a form switch/checkbox input
 * 
 * @param string $label      Label text
 * @param string $name       Input name attribute
 * @param string $value_yes  Value when checked
 * @param string $value_no   Value when unchecked (unused, for documentation)
 * @param string $checked    'checked' attribute if selected
 * 
 * @return string HTML for the form switch
 */
function make_form_check_input(string $label, string $name, string $value_yes, string $value_no, string $checked): string
{
    $escaped_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $escaped_value = htmlspecialchars($value_yes, ENT_QUOTES, 'UTF-8');
    $label_line = (!empty($label)) 
        ? "<label class='form-check-label' for='{$escaped_name}'>" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</label>" 
        : "";
    
    return <<<HTML
        <div class='form-check form-switch'>
            {$label_line}
            <input class='form-check-input' type='checkbox' name='{$escaped_name}' value='{$escaped_value}' {$checked}>
        </div>
    HTML;
}

/**
 * Generate an email icon button for messaging users
 * 
 * @param array<string,mixed> $tab       Translation record with user/lang/target info
 * @param string              $func_name JavaScript function to call on click
 * 
 * @return string HTML for the email button
 */
function make_mail_icon_new(array $tab, string $func_name = "pup_window_new"): string
{
    if (empty($func_name)) {
        $func_name = "pup_window_new";
    }
    
    $mail_params = [
        'user' => $tab['user'] ?? '',
        'lang' => $tab['lang'] ?? '',
        'target' => $tab['target'] ?? '',
        'date' => $tab['pupdate'] ?? '',
        'title' => $tab['title'] ?? '',
        'nonav' => '1'
    ];
    
    $mail_url = "index.php?ty=Emails/msg&" . http_build_query($mail_params, '', '&', PHP_QUERY_RFC3986);
    $escaped_url = htmlspecialchars($mail_url, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
    	<a class='btn btn-outline-primary btn-sm spannowrap' pup-target='{$escaped_url}' onclick='{$func_name}(this)'>@</a>
    HTML;
}

/**
 * Generate a project selection dropdown options
 * 
 * @param string $project Currently selected project
 * 
 * @return string HTML option elements
 */
function make_project_to_user(string $project): string
{
    $str = "<option value='Uncategorized'>Uncategorized</option>";
    
    foreach (TablesSql::$s_projects_title_to_id as $p_title => $p_id) {
        $selected = ($project === $p_title) ? "selected" : "";
        $escaped = htmlspecialchars($p_title, ENT_QUOTES, 'UTF-8');
        $str .= "<option value='{$escaped}' {$selected}>{$escaped}</option>";
    }
    
    return $str;
}

/**
 * Generate an input group with label
 * 
 * @param string $label    Input label
 * @param string $id       Input ID and name
 * @param string $value    Input value
 * @param string $required 'required' attribute or empty
 * 
 * @return string HTML for the input group
 */
function make_input_group(string $label, string $id, string $value, string $required): string
{
    $val2 = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $escaped_label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $escaped_id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
    <div class='col-md-3'>
        <div class='input-group mb-3'>
            <span class='input-group-text'>{$escaped_label}</span>
            <input class='form-control' type='text' name='{$escaped_id}' value='{$val2}' {$required}/>
        </div>
    </div>
    HTML;
}

/**
 * Generate an input group without column wrapper
 * 
 * @param string $label    Input label
 * @param string $id       Input ID and name
 * @param string $value    Input value
 * @param string $required 'required' attribute or empty
 * 
 * @return string HTML for the input group
 */
function make_input_group_no_col(string $label, string $id, string $value, string $required): string
{
    $val2 = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $escaped_label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $escaped_id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
    <div class='input-group mb-3'>
        <span class='input-group-text'>{$escaped_label}</span>
        <input class='form-control' type='text' name='{$escaped_id}' value='{$val2}' {$required}/>
    </div>
    HTML;
}

/**
 * Generate a dropdown select element
 * 
 * @param array<int,string> $tab  Options array
 * @param string            $cat  Currently selected value
 * @param string            $id   Select element ID and name
 * @param string            $add  Additional option (e.g., 'all')
 * 
 * @return string HTML for the select element
 */
function makeDropdown(array $tab, string $cat, string $id, string $add): string
{
    $options = "";
    
    foreach ($tab as $dd) {
        $se = ($cat === $dd) ? 'selected' : '';
        $escaped = htmlspecialchars($dd, ENT_QUOTES, 'UTF-8');
        $options .= "<option value='{$escaped}' {$se}>{$escaped}</option>";
    }
    
    $sel_line = "";
    if (!empty($add)) {
        $add2 = ($add === 'all') ? 'All' : $add;
        $sel = ($cat === $add) ? "selected" : "";
        $escaped_add = htmlspecialchars($add, ENT_QUOTES, 'UTF-8');
        $escaped_add2 = htmlspecialchars($add2, ENT_QUOTES, 'UTF-8');
        $sel_line = "<option value='{$escaped_add}' {$sel}>{$escaped_add2}</option>";
    }
    
    $escaped_id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
        <select dir="ltr" id="{$escaped_id}" name="{$escaped_id}" class="form-select" data-bs-theme="auto">
            {$sel_line}
            {$options}
        </select>
    HTML;
}

/**
 * Generate a card component with header and body
 * 
 * @param string $title Card header title
 * @param string $table Card body content
 * 
 * @return string HTML for the card
 */
function makeCard(string $title, string $table): string
{
    $escaped_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
    <div class="card">
        <div class="card-header aligncenter" style="font-weight:bold;">
            {$escaped_title}
        </div>
        <div class="card-body1 card2">
            {$table}
        </div>
    </div>
    HTML;
}

/**
 * Generate a card in a column layout
 * 
 * @param string $title  Card header title
 * @param string $table  Card body content
 * @param int    $numb   Column width (1-12)
 * @param string $table2 Additional content below card
 * @param string $title2 Header subtitle
 * 
 * @return string HTML for the column with card
 */
function makeColSm4(string $title, string $table, int $numb = 4, string $table2 = '', string $title2 = ''): string
{
    $escaped_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
    <div class="col-md-{$numb}">
        <div class="card card2 mb-3">
            <div class="card-header">
                <span class="card-title" style="font-weight:bold;">
                    {$escaped_title}
                </span>
                <div style='float: right'>
                    {$title2}
                </div>
                <div class="card-tools">
                    <button type="button" class="btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body1 card2">
                {$table}
            </div>
        </div>
        {$table2}
    </div>
    HTML;
}

/**
 * Generate a card with centered header
 * 
 * @param string $title    Header title
 * @param string $subtitle Header subtitle
 * @param string $table    Card body content
 * @param int    $numb     Column width (1-12)
 * 
 * @return string HTML for the card
 */
function make_col_sm_body(string $title, string $subtitle, string $table, int $numb = 4): string
{
    $escaped_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $escaped_subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
    <div class="col-md-{$numb}">
        <div class="card">
            <div class="card-header aligncenter1">
                <span style="font-weight:bold;">{$escaped_title}</span> {$escaped_subtitle}
            </div>
            <div class="card-body card2">
                {$table}
            </div>
        </div>
        <br>
    </div>
    HTML;
}

/**
 * Generate dropdown option elements
 * 
 * @param array<string,string> $uxutable Options array (display => value)
 * @param string               $code     Currently selected value
 * 
 * @return string HTML option elements
 */
function make_drop(array $uxutable, string $code): string
{
    $options = "";
    
    foreach ($uxutable as $name => $cod) {
        $selected = ($code === $cod) ? "selected" : "";
        $escaped_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escaped_cod = htmlspecialchars($cod, ENT_QUOTES, 'UTF-8');
        $options .= "<option value='{$escaped_cod}' {$selected}>{$escaped_name}</option>";
    }
    
    return $options;
}

/**
 * Generate datalist option elements
 * 
 * @param array<string,string> $hyh Options array (display => value)
 * 
 * @return string HTML option elements
 */
function make_datalist_options(array $hyh): string
{
    $options = '';
    foreach ($hyh as $language => $code) {
        $escaped_lang = htmlspecialchars($language, ENT_QUOTES, 'UTF-8');
        $escaped_code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $options .= "<option value='{$escaped_code}'>{$escaped_lang}</option>";
    }
    return $options;
}

/**
 * Generate a link to an MDWiki page
 * 
 * @param string $title Page title
 * 
 * @return string HTML anchor element or original title if empty
 */
function make_mdwiki_title(string $title): string
{
    if (!empty($title)) {
        $encoded_title = rawurlencode(str_replace(' ', '_', $title));
        $escaped_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return "<a target='_blank' href='https://mdwiki.org/wiki/{$encoded_title}'>{$escaped_title}</a>";
    }
    return htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a link to an MDWiki category page
 * 
 * @param string $category Category name
 * 
 * @return string HTML anchor element or original category if empty
 */
function make_cat_url(string $category): string
{
    if (!empty($category)) {
        $encoded_category = rawurlencode(str_replace(' ', '_', $category));
        $escaped_category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        return "<a target='_blank' href='https://mdwiki.org/wiki/Category:{$encoded_category}'>{$escaped_category}</a>";
    }
    return htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a link to a user talk page
 * 
 * @param string $lang Language code
 * @param string $user Username
 * 
 * @return string HTML anchor element
 */
function make_talk_url(string $lang, string $user): string
{
    $escaped_lang = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
    $escaped_user = rawurlencode($user);
    return "<a target='_blank' href='//{$escaped_lang}.wikipedia.org/w/index.php?title=User_talk:{$escaped_user}'>talk</a>";
}

/**
 * Generate a Content Translation tool URL
 * 
 * @param string $title    Article title to translate
 * @param string $lang     Target language code
 * @param string $tr_type  Translation type ('lead' or 'all')
 * 
 * @return string Content Translation URL
 */
function make_translation_url(string $title, string $lang, string $tr_type): string
{
    $page = ($tr_type === 'all') ? "User:Mr. Ibrahem/{$title}/full" : "User:Mr. Ibrahem/{$title}";
    
    $params = [
        'page' => $page,
        'from' => "simple",
        'sx' => 'true',
        'to' => $lang,
        'targettitle' => $title
    ];
    
    $url = "//{$lang}.wikipedia.org/wiki/Special:ContentTranslation";
    $url .= "?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986) . "#/sx?previousRoute=dashboard&eventSource=direct_preselect";
    
    return $url;
}

/**
 * Generate a link to an MDWiki user page
 * 
 * @param string $user Username
 * 
 * @return string HTML anchor element or original username if empty
 */
function make_mdwiki_user_url(string $user): string
{
    if (!empty($user)) {
        $encoded_user = rawurlencode(str_replace(' ', '_', $user));
        $escaped_user = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
        return "<a href='https://mdwiki.org/wiki/User:{$encoded_user}'>{$escaped_user}</a>";
    }
    return htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a link to a Wikipedia article
 * 
 * @param string      $target  Article title
 * @param string      $lang    Language code
 * @param string      $name    Display name (optional, defaults to target)
 * @param bool        $deleted Whether to show deleted indicator
 * 
 * @return string HTML anchor element or original target if empty
 */
function make_target_url(string $target, string $lang, string $name = '', bool $deleted = false): string
{
    $display_name = (!empty($name)) ? $name : $target;
    
    if (!empty($target)) {
        $encoded_target = rawurlencode(str_replace(' ', '_', $target));
        $escaped_display = htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8');
        $escaped_lang = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
        
        $link = "<a target='_blank' href='https://{$escaped_lang}.wikipedia.org/wiki/{$encoded_target}'>{$escaped_display}</a>";

        if ($deleted) {
            $link .= ' <span class="text-danger">(DELETED)</span>';
        }
        return $link;
    }
    return htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate an alert div with multiple messages
 * 
 * @param array<int,string> $texts Messages to display
 * @param string            $type  Alert type (success, danger, warning, info, secondary)
 * 
 * @return string HTML for the alert div
 */
function div_alert(array $texts, string $type = "secondary"): string
{
    if (empty($texts)) {
        return "";
    }
    
    if (empty($type)) {
        $type = "secondary";
    }
    
    $allowed_types = ['success', 'danger', 'warning', 'info', 'secondary', 'primary', 'light', 'dark'];
    if (!in_array($type, $allowed_types, true)) {
        $type = "secondary";
    }
    
    $messages = "";
    foreach ($texts as $text) {
        $messages .= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "<br>";
    }
    
    return "<div class='container m-1'><div class='alert alert-{$type}' role='alert'>{$messages}</div></div>";
}

/**
 * Generate an edit icon button
 * 
 * @param string              $target       Edit target route
 * @param array<string,mixed> $edit_params  Parameters for the edit URL
 * @param string              $text         Button text
 * 
 * @return string HTML for the edit button
 */
function make_edit_icon_new(string $target, array $edit_params, string $text = "Edit"): string
{
    if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
        $edit_params['test'] = 1;
    }
    
    $edit_params['nonav'] = 1;
    
    $edit_url = "index.php?ty={$target}&" . http_build_query($edit_params, '', '&', PHP_QUERY_RFC3986);
    
    if (empty($text)) {
        $text = "Edit";
    }
    
    $class_sm = ($text === "Edit") ? "btn-sm" : "";
    $escaped_url = htmlspecialchars($edit_url, ENT_QUOTES, 'UTF-8');
    $escaped_text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
		<a class='btn btn-outline-primary {$class_sm}' pup-target='{$escaped_url}' onclick='pup_window_new(this)'>{$escaped_text}</a>
	HTML;
}
