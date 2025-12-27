<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->get();
        return view('admin.banner.index', compact('banners'));
    }

    public function create()
    {
        // Return the create-edit view without $banner variable
        return view('admin.banner.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'html' => 'required',
            'css' => 'required',
        ]);

        $validated['is_active'] = true;

        // Generate preview image
        $previewPath = $this->generatePreviewImage($validated['html'], $validated['css']);
        $validated['preview_image'] = $previewPath;

        Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner created successfully!');
    }

    public function edit(Banner $banner)
    {
        // Return the create-edit view with $banner variable
        return view('admin.banner.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'html' => 'required',
            'css' => 'required',
        ]);

        // Delete old preview image if exists
        if ($banner->preview_image && file_exists(public_path($banner->preview_image))) {
            unlink(public_path($banner->preview_image));
        }

        // Regenerate preview image
        $previewPath = $this->generatePreviewImage($validated['html'], $validated['css']);
        $validated['preview_image'] = $previewPath;

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner updated successfully!');
    }

    public function destroy(Banner $banner)
    {
        // Delete preview image if exists
        if ($banner->preview_image && file_exists(public_path($banner->preview_image))) {
            unlink(public_path($banner->preview_image));
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully!');
    }

    /**
     * Toggle banner active status
     */
    public function toggle(Banner $banner)
    {
        $banner->update([
            'is_active' => !$banner->is_active
        ]);

        $status = $banner->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner {$status} successfully!");
    }

    /**
     * Generate preview image from HTML/CSS
     */
    private function generatePreviewImage($html, $css)
    {
        // Create banners directory if it doesn't exist
        $bannerDir = public_path('banners');
        if (!file_exists($bannerDir)) {
            mkdir($bannerDir, 0755, true);
        }

        // Generate unique filename
        $filename = 'banner_' . time() . '_' . Str::random(10) . '.jpg';
        $filepath = $bannerDir . '/' . $filename;

        // Strip animation classes and properties for static preview
        $staticCss = $this->removeAnimations($css);

        // Create complete HTML document
        $fullHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' rel='stylesheet'>
                <style>
                    * { box-sizing: border-box; }
                    body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                    /* Force all animations to show final state */
                    * {
                        animation: none !important;
                        opacity: 1 !important;
                        transform: none !important;
                    }
                    {$staticCss}
                </style>
            </head>
            <body>
                {$html}
            </body>
            </html>
        ";

        try {
            // Generate screenshot using Browsershot
            Browsershot::html($fullHtml)
                ->windowSize(1200, 630) // Standard banner size
                ->setScreenshotType('jpeg', 90) // JPEG with 90% quality
                ->waitUntilNetworkIdle() // Wait for all resources to load
                ->setDelay(500) // Additional delay to ensure rendering
                ->save($filepath);

            return 'banners/' . $filename;
        } catch (\Exception $e) {
            \Log::error('Banner preview generation failed: ' . $e->getMessage());

            // Create a simple placeholder if screenshot fails
            $this->createPlaceholderImage($filepath);
            return 'banners/' . $filename;
        }
    }

    /**
     * Remove animation-related CSS to show final state
     */
    private function removeAnimations($css)
    {
        // Remove @keyframes blocks
        $css = preg_replace('/@keyframes\s+[\w-]+\s*\{[^}]*\}/s', '', $css);

        // Remove animation classes
        $css = preg_replace('/\.animate-[\w-]+\s*\{[^}]*\}/s', '', $css);

        // Remove delay classes
        $css = preg_replace('/\.delay-\d+\s*\{[^}]*\}/s', '', $css);

        return $css;
    }

    /**
     * Create a simple placeholder image if screenshot fails
     */
    private function createPlaceholderImage($filepath)
    {
        try {
            // Create a simple colored rectangle as placeholder
            $image = imagecreatetruecolor(1200, 630);
            $bgColor = imagecolorallocate($image, 102, 126, 234); // #667eea
            $textColor = imagecolorallocate($image, 255, 255, 255);

            imagefilledrectangle($image, 0, 0, 1200, 630, $bgColor);

            // Add text
            $text = "Banner Preview";
            imagestring($image, 5, 520, 310, $text, $textColor);

            // Save as JPEG
            imagejpeg($image, $filepath, 90);
            imagedestroy($image);
        } catch (\Exception $e) {
            \Log::error('Placeholder creation failed: ' . $e->getMessage());
        }
    }
}
