<?php

namespace App\Utils\Html;

use App\Tables\SqlTables\TablesSql;


/**
 * Generate a banner alert with danger styling
 *
 * @param string $text The alert message
 *
 * @return string HTML for the alert banner
 */
function banner_alert(string $text): string
{
    // $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return <<<HTML
        <div class='container'>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {$text}
            </div>
        </div>
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

    foreach (TablesSql::$sProjectsTitleToId as $pTitle => $pId) {
        $selected = ($project === $pTitle) ? "selected" : "";
        $escaped = htmlspecialchars($pTitle, ENT_QUOTES, 'UTF-8');
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
    return <<<HTML
    <div class='col-md-3'>
        <div class='input-group mb-3'>
            <span class='input-group-text'>{$label}</span>
            <input class='form-control' type='text' name='{$id}' value='{$val2}' {$required}/>
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
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');


    return <<<HTML
    <div class='input-group mb-3'>
        <span class='input-group-text'>{$label}</span>
        <input class='form-control' type='text' name='{$id}' value='{$val2}' {$required}/>
    </div>
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
    $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <div class="card">
        <div class="card-header aligncenter" style="font-weight:bold;">
            {$escapedTitle}
        </div>
        <div class="card-body1 card2">
            {$table}
        </div>
        <!-- <div class="card-footer"></div> -->
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
    $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $escapedSubtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <div class="col-md-{$numb}">
        <div class="card">
            <div class="card-header aligncenter1">
                <span style="font-weight:bold;">{$escapedTitle}</span> {$escapedSubtitle}
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
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedCod = htmlspecialchars($cod, ENT_QUOTES, 'UTF-8');
        $options .= "<option value='{$escapedCod}' {$selected}>{$escapedName}</option>";
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
        $escapedLang = htmlspecialchars($language, ENT_QUOTES, 'UTF-8');
        $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $options .= "<option value='{$escapedCode}'>{$escapedLang}</option>";
    }
    return $options;
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

    $allowedTypes = ['success', 'danger', 'warning', 'info', 'secondary', 'primary', 'light', 'dark'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = "secondary";
    }

    $messages = "";
    foreach ($texts as $text) {
        $messages .= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "<br>";
    }
    return "<div class='container m-1'><div class='alert alert-{$type}' role='alert'>{$messages}</div></div>";
}
