<?php

namespace App\Traits;

use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Facades\Log;

trait MarkdownHelper
{
    /**
     * Check for Markdown and Convert
     * 
     * @param null|string $input content to convert to markdown
     * @return string for markdown result
     */
    public function convertMarkdown(?string $input = null): string
    {
        if(empty($input)) {
            return '';
        }

        // HTML Exists?!
        $desc = html_entity_decode($input);
        if(preg_match('/<(br|p)\s*?\/?>/', $desc)) {
            // Return HTML Instead of Trying to Convert Markdown
            return $desc;
        }

        // Is Markdown?
        if($this->isMarkdown($input)) {
            // Clean Up Miscellaneous Extra Lines
            $clean = $this->cleanMarkdown($input);

            // Try/Catch Errors
            try {
                // Initialize Markdown Converter
                $converter = new CommonMarkConverter();
                $converted = $converter->convertToHtml($clean);
            } catch(\Exception $e) {
                $exception = $e->getMessage();
            } catch(\TypeError $e) {
                $exception = $e->getMessage();
            }

            // Exception Thrown?!
            if(!empty($exception)) {
                Log::error("Exception occurred trying to convert markdown: " . $e->getMessage());
                Log::warning("Couldn't convert markdown: " . $clean);
                $converted = $clean;
            }

            // Convert Markdown to HTML
            $description = preg_replace('/\\\\/', '<br>', $converted);
            if(strpos($description, '<br>') === FALSE && strpos($description, '<p>') === FALSE) {
                $description = preg_replace('/(.)\R(.)/m', '$1<br>$2', $description);
            }

            // Return
            return $description;
        }

        // Add New Line to BR Instead
        return !empty($input) ? nl2br($input) : '';
    }

    /**
     * Is Text Markdown?
     * 
     * @param string $input
     * @return bool
     */
    public function isMarkdown(string $input): bool {
        // Check All Formats of Markdown
        preg_match('/\*{2}\S(.*?)\S\*{2}/', $input, $str);
        preg_match('/_\S(.*?)\S_/', $input, $em);
        preg_match('/^\s{4}\S/m', $input, $code);
        preg_match('/^(\s{0,3})?\#{2,6}\s+/m', $input, $head);
        preg_match('/\\\\/', $input, $eol);
        preg_match('/\[.*?\]\[\d+\]/', $input, $link);

        // Check For Markdown Formatting
        if(!empty($str[0]) || !empty($em[0]) || !empty($code[0]) ||
           !empty($head[0]) || !empty($eol[0]) || !empty($link[0])) {
            return true;
        }

        // No Markdown Detected
        return false;
    }

    /**
     * Clean Markdown Text
     * 
     * @param string $input
     * @return string
     */
    public function cleanMarkdown(string $input): string {
        // Clean Backslashes
        $backslash = str_replace('\\\\', '', $input);

        // Clean Asterisks
        $asterisk = str_replace('****', '', $backslash);

        // Clean Underscores
        $underscore = str_replace('__', '', $asterisk);

        // Return Result
        return $underscore;
    }

    /**
     * Strip Markdown From Text
     * 
     * @param string $input
     * @returun string
     */
    public function stripMarkdown(string $input): string {
        // Check For Markdown Formatting
        if($this->isMarkdown($input)) {
            // Clean Up Multiple Lines
            $input = preg_replace("/\R+/m", "\n", $input);
            $input = preg_replace("/\\\\/", "", $input);

            // Clean Up Strong and Em
            $input = preg_replace("/\*{2}(\S.*?\S)\*{2}/", "$1", $input);
            $input = preg_replace("/_(\S.*?\S)_/", "$1", $input);

            // Clean Up Headers
            $input = preg_replace("/^(\s{0,3})?\#{2,6}\s+/m", "", $input);

            // Clean Up Blockquotes and Codes
            $input = preg_replace("/^\s{4}(\S)/m", "$1", $input);

            // Clean Up Links
            $input = preg_replace("/\[(.*?)\]\[\d+\]/", "$1", $input);

            // Clean Tags
            return strip_tags($input);
        }

        // Return Result
        return $input;
    }
}
