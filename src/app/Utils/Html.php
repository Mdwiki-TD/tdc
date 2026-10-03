<?php

namespace App\Utils;

use App\Tables\SqlTables\TablesSql;

class Html
{
    public static function make_modal_fade(string $label, string $text, string $id, string $button = ''): string
    {
        $randomId = 'modalLabel' . random_int(1000, 9999);

        return <<<HTML

        <!-- Logout Modal-->
        <div class="modal fade" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="{$randomId}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="{$randomId}">{$label}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">{$text}</div>
                    <div class="modal-footer">
                        {$button}
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    HTML;
    }

    public static function makeDropdown(array $tab, string $cat, string $id, string $add): string
    {
        $options = "";

        foreach ($tab as $dd) {
            $se = ($cat === $dd) ? 'selected' : '';

            if (empty($dd)) continue;

            $escaped = htmlspecialchars($dd, ENT_QUOTES, 'UTF-8');
            $options .= "<option value='{$escaped}' {$se}>{$escaped}</option>";
        }

        $selLine = "";
        if (!empty($add)) {
            $add2 = ($add === 'all') ? 'All' : $add;
            $sel = ($cat === $add) ? "selected" : "";
            $escapedAdd = htmlspecialchars($add, ENT_QUOTES, 'UTF-8');
            $escapedAdd2 = htmlspecialchars($add2, ENT_QUOTES, 'UTF-8');
            $selLine = "<option value='{$escapedAdd}' {$sel}>{$escapedAdd2}</option>";
        }

        return <<<HTML
        <select dir="ltr" id="{$id}" name="{$id}" class="form-select" data-bs-theme="auto">
            {$selLine}
            {$options}
        </select>
    HTML;
    }

    public static function makeColSm4(string $title, string $table, int $numb = 4, string $table2 = '', string $title2 = ''): string
    {
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <div class="col-md-{$numb}">
            <div class="card card2 mb-3">
                <div class="card-header">
                    <span class="card-title" style="font-weight:bold;">
                        {$escapedTitle}
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
                <!-- <div class="card-footer"></div> -->
            </div>
            {$table2}
        </div>
    HTML;
    }

    public static function makeCol($title, $table, $table2)
    {
        return <<<HTML
        <div class="col-lg-3 col-md-12 col-sm-12">
            <div class="row">
                <div class="col-lg-12 col-md-6">
                    <div class="card card2 mb-3">
                        <div class="card-header">
                            <span class="card-title" style="font-weight:bold;">
                                $title
                            </span>
                            <div class="card-tools">
                                <button type="button" class="btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body1 card2">
                            $table
                        </div>
                        <!-- <div class="card-footer"></div> -->
                    </div>
                </div>
                <div class="col-lg-12 col-md-6">
                    $table2
                </div>
            </div>
        </div>
    HTML;
    }

    public static function banner_alert(string $text): string
    {
        return <<<HTML
        <div class='container'>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {$text}
            </div>
        </div>
    HTML;
    }

    public static function make_project_to_user(string $project): string
    {
        $str = "<option value='Uncategorized'>Uncategorized</option>";

        foreach (TablesSql::$sProjectsTitleToId as $pTitle => $pId) {
            $selected = ($project === $pTitle) ? "selected" : "";
            $escaped = htmlspecialchars($pTitle, ENT_QUOTES, 'UTF-8');
            $str .= "<option value='{$escaped}' {$selected}>{$escaped}</option>";
        }

        return $str;
    }

    public static function make_input_group(string $label, string $id, string $value, string $required): string
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

    public static function make_input_group_no_col(string $label, string $id, string $value, string $required): string
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

    public static function makeCard(string $title, string $table): string
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

    public static function make_col_sm_body(string $title, string $subtitle, string $table, int $numb = 4): string
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

    public static function make_drop(array $uxutable, string $code): string
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

    public static function make_datalist_options(array $hyh): string
    {
        $options = '';
        foreach ($hyh as $language => $code) {
            $escapedLang = htmlspecialchars($language, ENT_QUOTES, 'UTF-8');
            $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $options .= "<option value='{$escapedCode}'>{$escapedLang}</option>";
        }
        return $options;
    }

    public static function div_alert(array $texts, string $type = "secondary"): string
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

    public static function make_mdwiki_href($title)
    {
        if (empty($title)) return $title;

        return "https://mdwiki.org/wiki/" . rawurlencode(str_replace(' ', '_', $title));
    }

    public static function make_mdwiki_user_url(string $user): string
    {
        if (!empty($user)) {
            $encodedUser = rawurlencode(str_replace(' ', '_', $user));
            $escapedUser = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
            return "<a href='https://mdwiki.org/wiki/User:{$encodedUser}' taget='_blank'>{$escapedUser}</a>";
        }
        return $user;
    }

    public static function make_mdwiki_article_url_blank($title, $name = null)
    {
        if (empty($title)) return $title;

        $displayName = $name ? $name : $title;

        $encoded_title = rawurlencode(str_replace(' ', '_', $title));

        return "<a target='_blank' href='https://mdwiki.org/wiki/$encoded_title'>$displayName</a>";
    }

    public static function make_mdwiki_cat_url($category, $name = null)
    {
        if (empty($category)) return $category;

        $new_cat = str_replace('Category:', '', $category);

        $displayName = $name ? $name : $new_cat;

        $encoded_category = rawurlencode(str_replace(' ', '_', $new_cat));

        return "<a target='_blank' href='https://mdwiki.org/wiki/Category:$encoded_category'>$displayName</a>";
    }

    public static function make_wikipedia_url_blank($target, $lang, $name = '', $deleted = false)
    {
        if (empty($target)) return $target;

        $displayName = (!empty($name)) ? $name : $target;

        $encodedTarget = rawurlencode(str_replace(' ', '_', $target));

        $link = "<a target='_blank' href='https://$lang.wikipedia.org/wiki/$encodedTarget'>$displayName</a>";

        if ($deleted == 1) {
            $link .= ' <span class="text-danger">(DELETED)</span>';
        }

        return $link;
    }

    public static function make_wikidata_url_blank($qid, $name = '', $default = '')
    {
        if (empty($qid)) return $default;

        $displayName = (!empty($name)) ? $name : $qid;

        $url = "https://wikidata.org/wiki/" . rawurlencode(str_replace(' ', '_', $qid));

        return "<a class='inline' target='_blank' href='$url'>$displayName</a>";
    }

    public static function make_cat_url(string $category): string
    {
        if (!empty($category)) {
            $encodedCategory = rawurlencode(str_replace(' ', '_', $category));
            $escapedCategory = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
            return "<a target='_blank' href='https://mdwiki.org/wiki/Category:{$encodedCategory}'>{$escapedCategory}</a>";
        }
        return $category;
    }

    public static function make_talk_url(string $lang, string $user): string
    {
        $escapedLang = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
        $escapedUser = rawurlencode($user);
        return "<a target='_blank' href='//{$escapedLang}.wikipedia.org/w/index.php?title=User_talk:{$escapedUser}'>talk</a>";
    }

    public static function make_target_url(string $target, string $lang, string $name = '', bool $deleted = false): string
    {
        $displayName = (!empty($name)) ? $name : $target;

        if (!empty($target)) {
            $encodedTarget = rawurlencode(str_replace(' ', '_', $target));
            $escapedDisplay = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
            $escapedLang = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');

            $link = "<a target='_blank' href='https://{$escapedLang}.wikipedia.org/wiki/{$encodedTarget}'>{$escapedDisplay}</a>";

            if ($deleted) {
                $link .= ' <span class="text-danger">(DELETED)</span>';
            }
            return $link;
        }
        return $target;
    }

    public static function make_mdwiki_title(string $title): string
    {
        if (!empty($title)) {
            $encodedTitle = rawurlencode(str_replace(' ', '_', $title));
            $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            return "<a target='_blank' href='https://mdwiki.org/wiki/{$encodedTitle}'>{$escapedTitle}</a>";
        }
        return $title;
    }

    public static function make_edit_icon_new(string $target, array $editParams, string $text = "Edit"): string
    {
        if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
            $editParams['test'] = 1;
        }

        $editParams['nonav'] = 1;

        $editUrl = "index.php?ty={$target}&" . http_build_query($editParams, '', '&', PHP_QUERY_RFC3986);

        if (empty($text)) {
            $text = "Edit";
        }

        $classSm = ($text === "Edit") ? "btn-sm" : "";
        $escapedUrl = htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        return <<<HTML
		<a class='btn btn-outline-primary {$classSm}' pup-target='{$escapedUrl}' onclick='pup_window_new(this)'>{$text}</a>
	HTML;
    }

    public static function make_mail_icon_new(array $tab, string $funcName = "pup_window_new"): string
    {
        if (empty($funcName)) {
            $funcName = "pup_window_new";
        }

        $mailParams = [
            'user' => $tab['user'] ?? '',
            'lang' => $tab['lang'] ?? '',
            'target' => $tab['target'] ?? '',
            'date' => $tab['pupdate'] ?? '',
            'title' => $tab['title'] ?? '',
            'nonav' => '1'
        ];

        $mailUrl = "index.php?ty=msg&" . http_build_query($mailParams, '', '&', PHP_QUERY_RFC3986);
        $escapedUrl = htmlspecialchars($mailUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
	<a class='btn btn-outline-primary btn-sm spannowrap' pup-target='{$escapedUrl}' onclick='{$funcName}(this)'>@</a>
    HTML;
    }
}
