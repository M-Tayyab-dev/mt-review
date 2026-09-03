# MT Reviews Slider

MT Reviews Slider is a lightweight, fully customizable WordPress plugin that displays your customer reviews in a responsive grid or a smooth, auto-playing carousel slider. It includes a dedicated, tabbed settings dashboard, interactive rating star management, custom read-more action buttons with icon uploads, and full popup modals for longer reviews.

## Features

- **Custom Post Type:** Easily add, edit, and manage reviews right from your WordPress dashboard under the "Reviews" menu.
- **Flexible Display Layouts:** Choose between a dynamic carousel slider or a clean static grid layout via the plugin settings.
- **Tabbed Settings Dashboard:** A streamlined administrative panel separated into **General Settings** and **Slider Settings** with instant AJAX tab switching.
- **Carousel Controls:** Configure slides per view (1 to 4 columns), total posts limit, autoplay moving speed (in milliseconds), and pause-on-hover functionality.
- **Navigation & Pagination:** Built-in customizable navigation arrows and pagination bullets styled to match your accent theme.
- **Styling Customization:** Full color pickers for text, card backgrounds, borders, hover states, and navigation arrows, alongside configurable border radius and comment line clamps.
- **Read More Modals:** Automatically clips lengthy reviews to a set number of lines with a button that triggers a clean popup modal showing the full review content.
- **Custom Icon Uploads:** Upload or choose custom button icons directly from the WordPress Media Library.
- **Responsive Design:** Optimized layout scaling seamlessly for desktop, tablet (1024px and below), and mobile devices (768px and below).
- **Hardened by Default:** Sanitized and validated settings, escaped output, nonce/capability-checked saves, and a pinned, integrity-checkable CDN dependency. See [Security & Hardening](#security--hardening) below.

## Installation

1. Download or clone the plugin repository/folder.
2. Upload the `mt-reviews-slider` folder to your `/wp-content/plugins/` directory, or upload the ZIP file via **Plugins > Add New > Upload Plugin** in your WordPress admin dashboard.
3. Activate the plugin through the **Plugins** menu in WordPress.

## Usage

### 1. Adding Reviews

> **Note:** Managing reviews requires the **Editor** or **Administrator** role (or an equivalent custom role with page-editing capabilities). This keeps review content — which is rendered publicly via the shortcode — restricted to trusted users.

1. Go to **Reviews > Add New** in your WordPress dashboard.
2. Enter the reviewer's name in the title field and their review text in the main content editor.
3. Set the star rating (1 to 5) using the meta box on the right sidebar.
4. Upload or select a reviewer avatar using the **Featured Image** meta box.
5. Click **Publish**.

### 2. Displaying Reviews via Shortcode

Paste the following shortcode into any page, post, or Elementor shortcode widget where you want the reviews to appear:

```text
[mt_reviews_slider]
```

## Settings

Navigate to **Reviews > Settings** in your WordPress dashboard to configure the plugin options across two tabs:

- **General Settings:**
  - Text Color
  - Card Background Color
  - Border Color
  - Accent / Button Hover Color
  - Close Icon Hover Color
  - Button Icon Image (Media Uploader)
  - Read More Button Text
  - Border Radius (e.g., `10px` — validated to a plain CSS length; invalid input falls back to the default)
  - Max Lines of Comment before truncation

- **Slider Settings:**
  - Enable Slider Carousel (Yes/No)
  - Reviews to Show per Row (Columns 1–4)
  - Total Reviews to Display (`-1` for all)
  - Slider Moving Speed / Time in Milliseconds (e.g., `3000`)
  - Pause on Hover (Yes/No)
  - Arrow Navigation Color

Only users with the `manage_options` capability (Administrators by default) can access this settings page.

## Security & Hardening

This plugin follows WordPress security best practices throughout: nonce-verified form submissions, capability checks before any save, `sanitize_callback`s on every registered setting, and escaped output (`esc_html`, `esc_attr`, `esc_url`, `esc_js`) everywhere settings are rendered. A few additional hardening details worth knowing about:

- **Review management capability:** The `mt_review` post type uses `capability_type => 'page'`, so only Editors and Administrators can create or edit reviews — not any role with generic post-editing rights. Adjust this in the plugin file if your site needs a different role to manage reviews.
- **Border radius validation:** The `mt_border_radius` setting is checked against a strict `number + unit` pattern (e.g. `10px`, `1.5em`, `50%`) before being saved, preventing malformed values from being injected into the plugin's inline CSS.
- **Review content output:** Review text is always stripped of HTML tags and HTML-escaped before being displayed, so it renders safely as plain text regardless of what's typed into the editor.
- **Pinned CDN dependency:** Swiper.js/CSS are loaded from jsDelivr at a fixed version (not a floating `@10` tag), so the exact file served won't change without an explicit update to the plugin.
- **Optional Subresource Integrity (SRI):** The plugin includes ready-to-use filters to add `integrity`/`crossorigin` attributes to the Swiper `<script>`/`<link>` tags. To enable this extra layer of protection against CDN tampering, generate the `sha384-...` hash for the pinned Swiper version (e.g. via [srihash.org](https://www.srihash.org/)) and set the `MT_SWIPER_JS_SRI` / `MT_SWIPER_CSS_SRI` constants near the top of the plugin file.

If you discover a security issue with this plugin, please report it privately to the author rather than filing a public issue.
