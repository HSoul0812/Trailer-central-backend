<?php

namespace App\Helpers;

/**
 * Class ImageHelper
 * @package App\Helpers
 */
class ImageHelper
{
    /**
     * @param string $file
     * @param int $width
     * @param int $height
     * @param bool $proportional
     * @param string|null $output
     * @return bool|resource
     */
    public function resize(
        string $file,
        int $width = 800,
        int $height = 800,
        bool $proportional = true,
        ?string $output = 'file'
    ) {
        if($height <= 0 && $width <= 0) {
            return false;
        }

        $size = @getimagesize($file);

        $width_old = 0;
        $height_old = 0;
        $orientation = 0;

        // We using imagick because php size method doesn't get proper w/h if images are rotated in iOS / Mac
        try {
            $imagick = new \Imagick($file);

            $width_old = $imagick->getImageWidth();
            $height_old = $imagick->getImageHeight();
            $orientation = $imagick->getImageOrientation();
        } catch (\Exception $exception) {
            list($width_old, $height_old) = $size;
        }

        if($proportional) {
            if($width == 0) {
                $factor = $height / $height_old;
            } elseif($height == 0) {
                $factor = $width / $width_old;
            } else {
                $factor = min($width / $width_old, $height / $height_old);
            }

            $final_width  = round($width_old * $factor);
            $final_height = round($height_old * $factor);

        } else {
            $final_width  = ($width <= 0) ? $width_old : $width;
            $final_height = ($height <= 0) ? $height_old : $height;
        }

        switch($size[2]) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                break;
            default:
                return false;
        }

        if($width_old < $width && $height_old < $height) {
            $final_width  = $width_old;
            $final_height = $height_old;
        }

        $image_resized = imagecreatetruecolor($final_width, $final_height);

        if(($size[2] == IMAGETYPE_GIF) || ($size[2] == IMAGETYPE_PNG)) {
            $trnprt_indx = imagecolortransparent($image);

            // If we have a specific transparent color
            if($trnprt_indx >= 0) {

                // Get the original image's transparent color's RGB values
                $trnprt_color = imagecolorsforindex($image, $trnprt_indx);

                // Allocate the same color in the new image resource
                $trnprt_indx = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                // Completely fill the background of the new image with allocated color.
                imagefill($image_resized, 0, 0, $trnprt_indx);

                // Set the background color for new image to transparent
                imagecolortransparent($image_resized, $trnprt_indx);


            } // Always make a transparent background color for PNGs that don't have one allocated already
            elseif($size[2] == IMAGETYPE_PNG) {

                // Turn off transparency blending (temporarily)
                imagealphablending($image_resized, false);

                // Create a new transparent color for image
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);

                // Completely fill the background of the new image with allocated color.
                imagefill($image_resized, 0, 0, $color);

                // Restore transparency blending
                imagesavealpha($image_resized, true);
            }
        }

        imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);

        switch($orientation) {
            case 3:
            case 4:
                $image_resized = imagerotate($image_resized, 180, 0);
                break;
            case 5:
            case 6:
                $image_resized = imagerotate($image_resized, -90, 0);
                break;
            case 7:
            case 8:
                $image_resized = imagerotate($image_resized, 90, 0);
                break;
        }

        @unlink($file);

        switch(strtolower($output)) {
            case 'browser':
                $mime = image_type_to_mime_type($size[2]);
                header("Content-type: $mime");
                $output = null;
                break;
            case 'file':
                $output = $file;
                break;
            case 'return':
                return $image_resized;
            default:
                break;
        }

        switch($size[2]) {
            case IMAGETYPE_GIF:
                imagegif($image_resized, $output);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image_resized, $output);
                break;
            case IMAGETYPE_PNG:
                imagepng($image_resized, $output);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * @param string $imgPath
     * @param string|null $bottomText
     * @param string|null $topText
     * @param string|null $output
     * @return bool
     */
    public function addOverlay(string $imgPath, ?string $bottomText = null, ?string $topText = null, ?string $output = 'file'): bool
    {
        $font = resource_path('fonts/IMPACT.TTF');

        $info = getimagesize($imgPath);

        switch($info[2]) {
            case IMAGETYPE_GIF:
                $im = imagecreatefromgif($imgPath);
                break;
            case IMAGETYPE_JPEG:
                $im = imagecreatefromjpeg($imgPath);
                break;
            case IMAGETYPE_PNG:
                $im = imagecreatefrompng($imgPath);
                break;
            default:
                return false;
        }

        $colors = array(
            'white' => imagecolorallocate($im, 0, 0, 0),
            'black' => imagecolorallocate($im, 0, 0, 0),
            'gray'  => imagecolorallocate($im, 128, 128, 128),
        );

        $margin = 10;
        $superMargin = 0;

        $topFontSize = 16;
        $topShowBorders = true;

        $bottomFontSize = 16;
        $bottomShowBorders = true;

        $overlayColors = array(
            'background' => $colors['black'],
            'border'     => imagecolorallocate($im, 128, 128, 128),
            'text'       => imagecolorallocate($im, 255, 255, 255)
        );

        // TODO I think we can just use $newWidth and $newHeight here
        $imgWidth = imagesx($im);
        $imgHeight = imagesy($im);

        if(!empty($topText)) {
            $topBox = imagettfbbox($topFontSize, 0, $font, $topText);
            $topWidth = abs($topBox[2] - $topBox[0]);
            $topHeight = abs($topBox[3] - $topBox[5]);

            imagefilledrectangle($im, 0, 0, $imgWidth, ($topHeight + $margin) + $superMargin, $overlayColors['background']);

            if($topShowBorders) {
                imageline($im, 0, 0, $imgWidth, 0, $overlayColors['border']);
                imageline($im, 0, ($topHeight + $margin) + $superMargin, $imgWidth, ($topHeight + $margin) + $superMargin, $overlayColors['border']);
            }

            imagettftext($im, $topFontSize, 0, round(($imgWidth - $topWidth) / 2), round($topHeight + ($margin / 2) + $superMargin), $overlayColors['text'],
                $font, $topText);

        }

        if(!empty($bottomText)) {
            $bottomBox = imagettfbbox($bottomFontSize, 0, $font, $bottomText);
            $bottomWidth = abs($bottomBox[2] - $bottomBox[0]);
            $bottomHeight = abs($bottomBox[3] - $bottomBox[5]);

            imagefilledrectangle($im, 0, $imgHeight, $imgWidth, ($imgHeight - ($bottomHeight + $margin + $superMargin)), $overlayColors['background']);

            if($bottomShowBorders) {
                imageline($im, 0, ($imgHeight - ($bottomHeight + $margin + $superMargin)), $imgWidth, ($imgHeight - ($bottomHeight + $margin + $superMargin)),
                    $overlayColors['border']);
                imageline($im, 0, $imgHeight - 1, $imgWidth, $imgHeight - 1, $overlayColors['border']);
            }

            imagettftext($im, $bottomFontSize, 0, round(($imgWidth - $bottomWidth) / 2), round($imgHeight - ($margin / 2) - $superMargin), $overlayColors['text'],
                $font, $bottomText);
        }

        switch(strtolower($output)) {
            case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = null;
                break;
            case 'file':
                $output = $imgPath;
                break;
            case 'return':
                return $im;
            default:
                break;
        }

        switch($info[2]) {
            case IMAGETYPE_GIF:
                imagegif($im, $output);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($im, $output);
                break;
            case IMAGETYPE_PNG:
                imagepng($im, $output);
                break;
            default:
                return false;
        }

        return true;
    }
}
