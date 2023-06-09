<?php

namespace App\Helpers;

use App\Models\User\User;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\Helpers\MissingOverlayLogoParametersException;
use Imagick, ImagickDraw;
use App\Services\CRM\Email\MosaicoServiceInterface;

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
            $palletsize = imagecolorstotal($image);

            // If we have a specific transparent color
            if($trnprt_indx >= 0 && $trnprt_indx < $palletsize) {

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

    /**
     * @param string $imagePath
     * @param string $text
     * @param array $params
     * @return string local path of new image
     */
    public function addUpperTextOverlay(string $imagePath, string $text, array $params)
    {
        $font = resource_path('fonts/IMPACT.TTF');
        list($imageWidth, $imageHeight, $imageType) = getimagesize($imagePath);
        $imageResource = $this->getImageResource($imagePath, $imageType);
        $basicColors = $this->getBasicColors($imageResource);

        // Get BG & Border Color
        $bgColor = $borderColor = $basicColors['black'];
        if (isset($params['overlay_upper_bg'])) {

            $colors = $this->hex2rgb($params['overlay_upper_bg']);
            $alpha = $this->getAlphaFromPercent($params['overlay_upper_alpha']);
            $bgColor = $borderColor = imagecolorallocatealpha($imageResource, $colors[0], $colors[1], $colors[2], $alpha);
        }

        // Get Text Color
        $textColor = $basicColors['white'];
        if (isset($params['overlay_upper_text'])) {

            $colors = $this->hex2rgb($params['overlay_upper_text']);
            $textColor = imagecolorallocate($imageResource, $colors[0], $colors[1], $colors[2]);
        }

        // Get Text Size
        $textSize = 40;
        if (isset($params['overlay_upper_size']) && !empty($params['overlay_upper_size'])
            && is_numeric($params['overlay_upper_size'])) {

            $textSize = $params['overlay_upper_size'];
        }

        // Get Text Margin
        $textMargin = 40;
        if (isset($params['overlay_upper_margin']) && !empty($params['overlay_upper_margin'])
            && is_numeric($params['overlay_upper_margin'])) {

            $textMargin = $params['overlay_upper_margin'];
        }

        // Start Apply Overlay
        $topBox = imagettfbbox($textSize, 0, $font, $text);
        $topWidth = abs($topBox[2] - $topBox[0]);
        $topHeight = abs($topBox[3] - $topBox[5]);

        // Add Upper Overlay
        imagefilledrectangle($imageResource, 0, 0, $imageWidth, ($topHeight + $textMargin), $bgColor);

        // Add Upper Border
        imageline($imageResource, 0, 0, $imageWidth, 0, $borderColor);
        imageline($imageResource, 0, ($topHeight + $textMargin), $imageWidth, ($topHeight + $textMargin), $borderColor);

        // Write Upper Text
        imagettftext($imageResource, $textSize, 0, round(($imageWidth - $topWidth) / 2), round($topHeight + ($textMargin / 2)), $textColor, $font, $text);

        // Paste back $imageResource;
        $imageContent = $this->getContentFromResource($imageResource, $imageType);
        $newImagePath = $this->createTempFile($imageContent, $imageType);

        return $newImagePath;
    }

    /**
     * @param string $imagePath
     * @param string $text
     * @param array $params
     * @return string local path of new image
     */
    public function addLowerTextOverlay(string $imagePath, string $text, array $params)
    {
        $font = resource_path('fonts/IMPACT.TTF');
        list($imageWidth, $imageHeight, $imageType) = getimagesize($imagePath);
        $imageResource = $this->getImageResource($imagePath, $imageType);
        $basicColors = $this->getBasicColors($imageResource);

        // Get Background & Border Color
        $bgColor = $borderColor = $basicColors['black'];
        if (isset($params['overlay_lower_bg'])) {

            $colors = $this->hex2rgb($params['overlay_lower_bg']);
            $alpha = $this->getAlphaFromPercent($params['overlay_lower_alpha']);
            $bgColor = $borderColor = imagecolorallocatealpha($imageResource, $colors[0], $colors[1], $colors[2], $alpha);
        }

        // Get Text Color
        $textColor = $basicColors['white'];
        if (isset($params['overlay_lower_text'])) {

            $colors = $this->hex2rgb($params['overlay_lower_text']);
            $textColor = imagecolorallocate($imageResource, $colors[0], $colors[1], $colors[2]);
        }

        // Get Text Size
        $textSize = 40;
        if (isset($params['overlay_lower_size']) && !empty($params['overlay_lower_size'])
            && is_numeric($params['overlay_lower_size'])) {

            $textSize = $params['overlay_lower_size'];
        }

        // Get Text Margin
        $textMargin = 40;
        if (isset($params['overlay_lower_margin']) && !empty($params['overlay_lower_margin'])
            && is_numeric($params['overlay_lower_margin'])) {

            $textMargin = $params['overlay_lower_margin'];
        }

        // Start Apply Overlay
        $bottomBox = imagettfbbox($textSize, 0, $font, $text);
        $bottomWidth = abs($bottomBox[2] - $bottomBox[0]);
        $bottomHeight = abs($bottomBox[3] - $bottomBox[5]);

        // Add Lower Overlay
        imagefilledrectangle($imageResource, 0, $imageHeight, $imageWidth, ($imageHeight - ($bottomHeight + $textMargin)), $bgColor);

        // Add Lower Border
        imageline($imageResource, 0, ($imageHeight - ($bottomHeight + $textMargin)), $imageWidth, ($imageHeight - ($bottomHeight + $textMargin)), $borderColor);
        imageline($imageResource, 0, $imageHeight - 1, $imageWidth, $imageHeight - 1, $borderColor);

        // Add Lower Text
        imagettftext($imageResource, $textSize, 0, round(($imageWidth - $bottomWidth) / 2), round($imageHeight - ($textMargin / 2)), $textColor, $font, $text);

        // Paste back $imageResource;
        $imageContent = $this->getContentFromResource($imageResource, $imageType);
        $newImagePath = $this->createTempFile($imageContent, $imageType);

        return $newImagePath;
    }

    /**
     * @param string $imagePath
     * @param string $logoPath
     * @param array $config
     * @return string local path of new image
     *
     * @throws MissingOverlayLogoParametersException when logo overlay is enabled and its configurations were not provided
     */
    public function addLogoOverlay(string $imagePath, string $logoPath, array $config)
    {
        if (!isset($config['overlay_logo_width'])
            || !isset($config['overlay_logo_height'])
            || !isset($config['overlay_logo_position'])) {
            throw new MissingOverlayLogoParametersException;
        }

        list($imageWidth, $imageHeight, $imageType) = getimagesize($imagePath);
        list($originalLogoWidth, $originalLogoHeight, $logoType) = getimagesize($logoPath);
        $logoResource = $this->getImageResource($logoPath, $logoType);
        $imageResource = $this->getImageResource($imagePath, $imageType);

        // Check Dimensions
        $logoWidth = preg_replace("/[^0-9.]/", "", $config['overlay_logo_width']);
        $logoHeight = preg_replace("/[^0-9.]/", "", $config['overlay_logo_height']);

        // Check for PX/% on Width
        if (strpos($config['overlay_logo_width'], "%") !== false) {
            $percentageWidth = $logoWidth * 0.01;

            if (!$logoHeight) {
                $logoWidth = $percentageWidth * $imageWidth * 0.33;
            } else {
                $logoWidth = $percentageWidth * $imageWidth;
            }
        }

        if($logoWidth > $originalLogoWidth || empty($logoWidth)) {
            $logoWidth = $originalLogoWidth;
        }

        // Check for PX/% on Height
        if (strpos($config['overlay_logo_height'], "%") !== false) {
            $percentageHeight = $logoHeight * 0.01;
            $logoHeight = $percentageHeight * $imageHeight;
        }

        if ($logoHeight > $originalLogoHeight) {
            $logoHeight = $originalLogoHeight;
        } elseif (empty($logoHeight)) {
            $logoHeight = -1;
        }

        // Create Local Logo Path
        $localLogoPath = $this->createTempFile($this->getContentFromResource($logoResource, $logoType), $imageType);

        // Create Resized Logo while keeping ratio
        $resizedLogo = $this->createTempFile('', $logoType);

        if (!preg_replace("/[^0-9.]/", "", $config['overlay_logo_height'])) {
            shell_exec('convert ' . $localLogoPath . ' -resize ' . $logoWidth . 'x' . ' ' . $resizedLogo);
        } else {
            shell_exec('convert ' . $localLogoPath . ' -resize ' . $logoWidth . 'x' . $logoHeight . ' ' . $resizedLogo);
        }


        // Get New Logo Dimensions
        $resizedLogoResource = $this->getImageResource($resizedLogo, $logoType);
        $logoNewWidth = imagesx($resizedLogoResource);
        $logoNewHeight = imagesy($resizedLogoResource);

        // Get X/Y Position
        $x = 5; $y = 5;
        switch ($config['overlay_logo_position']) {
            case User::OVERLAY_LOGO_POSITION_UPPER_RIGHT:
                $x = $imageWidth - $logoNewWidth - $x;
                break;
            case User::OVERLAY_LOGO_POSITION_LOWER_LEFT:
                $y = $imageHeight - $logoNewHeight - $y;
                break;
            case User::OVERLAY_LOGO_POSITION_LOWER_RIGHT:
                $x = $imageWidth - $logoNewWidth - $x;
                $y = $imageHeight - $logoNewHeight - $y;
                break;
        }

        // Create Local Image Path
        $localImagePath = $this->createTempFile($this->getContentFromResource($imageResource, $imageType), $imageType);

        // Add Logo to Image
        $newImagePath = $this->createTempFile('', $logoType);
        shell_exec('convert ' . $localImagePath . ' ' . $resizedLogo . ' -alpha on -compose src-over -geometry +' . $x . '+' . $y . ' -composite ' . $newImagePath);

        // Delete Tmp Files
        unlink($resizedLogo);
        unlink($localLogoPath);
        unlink($localImagePath);

        return $newImagePath;
    }

    /**
     * Encode URL if filename has whitespace
     *
     * @param string $url
     * @return string new url with encoded filename
     */
    public function encodeUrl(string $url)
    {
        $pos = strrpos($url, '/') + 1; // last occurance slash
        $result = substr($url, 0, $pos) . rawurlencode(substr($url, $pos));

        return $result;
    }

    /**
     * Create temp files
     *
     * @param string|null $fileContent
     * @param  int  $mimeType image type as integer commonly used by `getimagesize`, `exif_read_data`, `exif_thumbnail`, `exif_imagetype`
     * @return string new file path
     */
    protected function createTempFile(string $fileContent = '', $mimeType = null)
    {
        $randomFilename = $this->getRandomImageNameWithExtension($fileContent, $mimeType);

        Storage::disk('tmp')->put($randomFilename, $fileContent);

        return Storage::disk('tmp')->path($randomFilename);
    }

    /**
     * Create random string
     *
     * @return string
     */
    public function getRandomString()
    {
        return bin2hex(random_bytes(18));
    }

    /**
     * Creates random image name with a proper extension according to file content
     * @param  int  $mimeType image type as integer commonly used by `getimagesize`, `exif_read_data`, `exif_thumbnail`, `exif_imagetype`
     * @throws \Exception when an appropriate source of randomness cannot be found.
     */
    protected function getRandomImageNameWithExtension(string $fileContent, $mimeType = null): string
    {
        $mimeType= image_type_to_mime_type(((int)$mimeType) ?: 2); // to ensure it always is an integer and do not break something

        // we gonna use `jpeg` extension as fallback, it is not a problem because for S3 object it doesn't matter
        $extension = str_replace('image/', '', $mimeType);

        return sprintf('%s.%s', bin2hex(random_bytes(18)), $extension);
    }

    /**
     * @param string $imagePath
     * @param int $imageType
     * @return \GdImage
     */
    protected function getImageResource(string $imagePath, int $imageType)
    {
        switch($imageType) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($imagePath);
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($imagePath);
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                return $image;
            default:
                return false;
        }
    }

    /**
     * Handle Basic Colors
     *
     * @param string $image Image resource
     * @return array
     */
    protected function getBasicColors($image)
    {
        return [
            'white' => imagecolorallocate($image, 255, 255, 255),
            'black' => imagecolorallocate($image, 0, 0, 0),
            'gray'  => imagecolorallocate($image, 128, 128, 128),
        ];
    }

    /**
     * Convert Hexadecimal Color to RGB Array
     *
     * @param string $hex Full color hexadecimal.
     * @return array RGB array of converted hex.
     */
    protected function hex2rgb(string $hex) {
        // Remove Pound Sign
        $hex = str_replace("#", "", $hex);

        // 3-Hex?
        if(strlen($hex) == 3) {

            // Convert 3-to-RGB
            $r = hexdec(substr($hex,0,1) . substr($hex,0,1));
            $g = hexdec(substr($hex,1,1) . substr($hex,1,1));
            $b = hexdec(substr($hex,2,1) . substr($hex,2,1));

        } elseif (strlen($hex) == 6) {

            // Convert 6-to-RGB
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));

        } else {

            return false;
        }

        $rgb = array($r, $g, $b);

        // Return Hex Array
        return $rgb;
    }

    /**
     * Get Alpha From Transparent Percentage
     *
     * @param string|int $alpha Get Alpha Color from Percentage
     * @return string Formatted alpha color to add to the overlay
     */
    protected function getAlphaFromPercent($alpha)
    {
        // Clean Percentage
        $per = ($alpha * 0.01);

        // Calculate Transparency
        $max = 127;
        $transparency = ($per * $max);

        // Return Base Phone
        return $transparency;
    }

    /**
     *
     * @param string $resource Image resource
     * @param int $imageType
     * @return string
     */
    protected function getContentFromResource($resource, int $imageType)
    {
        ob_start();

        switch ($imageType) {
            case IMAGETYPE_GIF:
                imagegif($resource);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($resource);
                break;
            case IMAGETYPE_PNG:
                imagepng($resource);
                break;
        }

        $imageContent = ob_get_contents();

        ob_end_clean();

        return $imageContent;
    }

    /**
     * Get Image Placeholder
     * 
     * @param int $width
     * @param int $height
     * 
     * @return string file content
     */
    public function getImagePlaceholder($width, $height)
    {
        $image = new Imagick();

        $image->newImage( $width, $height, "#707070" );
        $image->setImageFormat( "png" );

        $x = 0;
        $y = 0;
        $size = 40;

        $draw = new ImagickDraw();

        while ( $y < $height )
        {
            $draw->setFillColor( "#808080" );

            $points = [
                [ "x" => $x, "y" => $y ],
                [ "x" => $x + $size, "y" => $y ],
                [ "x" => $x + $size * 2, "y" => $y + $size ],
                [ "x" => $x + $size * 2, "y" => $y + $size * 2 ]
            ];

            $draw->polygon( $points );

            $points = [
                [ "x" => $x, "y" => $y + $size ],
                [ "x" => $x + $size, "y" => $y + $size * 2 ],
                [ "x" => $x, "y" => $y + $size * 2 ]
            ];

            $draw->polygon( $points );

            $x += $size * 2;

            if ( $x > $width )
            {
                $x = 0;
                $y += $size * 2;
            }
        }

        $draw->setFillColor( "#B0B0B0" );
        $draw->setFontSize( $width / 5 );
        $draw->setFontWeight( 800 );
        $draw->setGravity( Imagick::GRAVITY_CENTER );
        $draw->annotation( 0, 0, $width . " x " . $height );

        $image->drawImage( $draw );

        return $image->getImageBlob();
    }

    /**
     * Alternative to resize() Method above without the original file getting deleted
     * 
     * @param string $file
     * @param int $width
     * @param int $height
     * 
     * @return string file content
     */
    public function resizeImage(string $file, int $width, int $height)
    {
        $image = new Imagick($file);
        $image->resizeImage( $width, $height, Imagick::FILTER_LANCZOS, 1.0 );

        return $image->getImageBlob();
    }

    /**
     * Not sure what this do
     * 
     * @param string $file
     * @param int $width
     * @param int $height
     * 
     * @return string file content
     */
    public function coverImage(string $file, int $width, int $height)
    {
        $image = new Imagick($file);

        $image_geometry = $image->getImageGeometry();

        $width_ratio = $image_geometry[ "width" ] / $width;
        $height_ratio = $image_geometry[ "height" ] / $height;

        $resize_width = $width;
        $resize_height = $height;

        if ( $width_ratio > $height_ratio )
            $resize_width = 0;
        else
            $resize_height = 0;

        $image->resizeImage( $resize_width, $resize_height, Imagick::FILTER_LANCZOS, 1.0 );

        $image_geometry = $image->getImageGeometry();

        $x = ( $image_geometry[ "width" ] - $width ) / 2;
        $y = ( $image_geometry[ "height" ] - $height ) / 2;

        $image->cropImage( $width, $height, $x, $y );

        return $image->getImageBlob();
    }

    /**
     * Create Thumbnail Image
     * 
     * @param string $file
     * @return string
     */
    public function createThumbnailImage(string $file)
    {
        $tempThumbnailPath = $this->createTempFile();
        $newImage = new Imagick($file);
        $newImage->resizeImage(MosaicoServiceInterface::THUMBNAIL_WIDTH, 
            MosaicoServiceInterface::THUMBNAIL_HEIGHT, Imagick::FILTER_LANCZOS, 1.0, TRUE );
        $newImage->writeImage($tempThumbnailPath);

        return $tempThumbnailPath;
    }

    /**
     * Get filesize() for Remote File
     */
    public function getRemoteFileSize(string $url)
    {
        $headers = get_headers($url, 1);
        $filesize = $headers["Content-Length"] ?? 0;
        return $filesize;
    }

    /**
     * @param  string  $localFilename
     * @return string the new filename with `jpeg` extension
     */
    public function convertWebpToJpeg(string $localFilename): string
    {
        $imageInfo = pathinfo($localFilename);
        $newLocalFilename = $imageInfo['dirname'].DIRECTORY_SEPARATOR.$imageInfo['filename'].'.jpeg';

        imagejpeg(imagecreatefromwebp($localFilename), $newLocalFilename);

        return $newLocalFilename;
    }
}
