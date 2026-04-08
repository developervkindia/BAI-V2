/**
 * BAI Docs - Document Editor (TinyMCE)
 * Vite entry point for the rich text document editor.
 * Note: CSS skins are NOT imported here (lightningcss can't parse TinyMCE's advanced selectors).
 * Instead, TinyMCE loads skins from the CDN via skin_url config.
 */

import tinymce from 'tinymce';

// Theme & UI
import 'tinymce/themes/silver';
import 'tinymce/models/dom';
import 'tinymce/icons/default';

// Plugins
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/codesample';
import 'tinymce/plugins/directionality';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/emoticons/js/emojis';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/image';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/pagebreak';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/quickbars';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/wordcount';

// Make tinymce available globally for Alpine.js integration
window.tinymce = tinymce;
