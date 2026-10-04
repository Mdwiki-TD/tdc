<?php

namespace App\Utils;

class HtmlUrls
{
    public static function make_mdwiki_href(?string $title): ?string
    {
        if (empty($title)) return $title;

        return "https://mdwiki.org/wiki/" . rawurlencode(str_replace(' ', '_', $title));
    }

    /**
     * Generate a link to an MDWiki user page
     *
     * @param string $user Username
     *
     * @return string HTML anchor element or original username if empty
     */
    public static function make_mdwiki_user_url(string $user): string
    {
        if (!empty($user)) {
            $encodedUser = rawurlencode(str_replace(' ', '_', $user));
            $escapedUser = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
            return "<a href='https://mdwiki.org/wiki/User:{$encodedUser}' taget='_blank'>{$escapedUser}</a>";
        }
        return $user;
    }

    public static function make_mdwiki_article_url_blank(?string $title, ?string $name = null): ?string
    {
        if (empty($title)) return $title;

        $displayName = $name !== null && $name !== '' ? $name : $title;

        $encoded_title = rawurlencode(str_replace(' ', '_', $title));

        return "<a target='_blank' href='https://mdwiki.org/wiki/$encoded_title'>$displayName</a>";
    }

    public static function make_mdwiki_cat_url(?string $category, ?string $name = null): ?string
    {
        if (empty($category)) return $category;

        $new_cat = str_replace('Category:', '', $category);

        $displayName = $name !== null && $name !== '' ? $name : $new_cat;

        $encoded_category = rawurlencode(str_replace(' ', '_', $new_cat));

        return "<a target='_blank' href='https://mdwiki.org/wiki/Category:$encoded_category'>$displayName</a>";
    }

    public static function make_wikipedia_url_blank(mixed $target, string $lang, mixed $name = '', mixed $deleted = false): mixed
    {
        if (empty($target)) return $target;

        $displayName = (!empty($name)) ? $name : $target;
        if (is_array($displayName)) {
            $displayName = implode(', ', $displayName);
        }

        $targetStr = is_array($target) ? implode('_', $target) : (string)$target;
        $encodedTarget = rawurlencode(str_replace(' ', '_', $targetStr));

        $link = "<a target='_blank' href='https://$lang.wikipedia.org/wiki/$encodedTarget'>$displayName</a>";

        if ($deleted == 1 || $deleted === true) {
            $link .= ' <span class="text-danger">(DELETED)</span>';
        }

        return $link;
    }

    public static function make_wikidata_url_blank(?string $qid, ?string $name = '', ?string $default = ''): ?string
    {
        if (empty($qid)) return $default;

        $displayName = (!empty($name)) ? $name : $qid;

        $url = "https://wikidata.org/wiki/" . rawurlencode(str_replace(' ', '_', $qid));

        return "<a class='inline' target='_blank' href='$url'>$displayName</a>";
    }

    /**
     * Generate a link to an MDWiki category page
     *
     * @param string $category Category name
     *
     * @return string HTML anchor element or original category if empty
     */
    public static function make_cat_url(string $category): string
    {
        if (!empty($category)) {
            $encodedCategory = rawurlencode(str_replace(' ', '_', $category));
            $escapedCategory = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
            return "<a target='_blank' href='https://mdwiki.org/wiki/Category:{$encodedCategory}'>{$escapedCategory}</a>";
        }
        return $category;
    }

    /**
     * Generate a link to a user talk page
     *
     * @param string $lang Language code
     * @param string $user Username
     *
     * @return string HTML anchor element
     */
    public static function make_talk_url(string $lang, string $user): string
    {
        $escapedLang = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
        $escapedUser = rawurlencode($user);
        return "<a target='_blank' href='//{$escapedLang}.wikipedia.org/w/index.php?title=User_talk:{$escapedUser}'>talk</a>";
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

    /**
     * Generate a link to an MDWiki page
     *
     * @param string $title Page title
     *
     * @return string HTML anchor element or original title if empty
     */
    public static function make_mdwiki_title(string $title): string
    {
        if (!empty($title)) {
            $encodedTitle = rawurlencode(str_replace(' ', '_', $title));
            $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            return "<a target='_blank' href='https://mdwiki.org/wiki/{$encodedTitle}'>{$escapedTitle}</a>";
        }
        return $title;
    }

    /**
     * Generate an edit icon button
     *
     * @param string              $target       Edit target route
     * @param array<string,mixed> $editParams  Parameters for the edit URL
     * @param string              $text         Button text
     *
     * @return string HTML for the edit button
     */
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


    /**
     * Generate an email icon button for messaging users
     *
     * @param array<string,mixed> $tab       Translation record with user/lang/target info
     * @param string              $funcName JavaScript function to call on click
     *
     * @return string HTML for the email button
     */
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
