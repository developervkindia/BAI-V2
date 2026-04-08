/**
 * BAI Docs - Spreadsheet Editor (jspreadsheet-ce)
 * Vite entry point for the spreadsheet editor.
 */

import jspreadsheet from 'jspreadsheet-ce';
import 'jspreadsheet-ce/dist/jspreadsheet.css';

// Make jspreadsheet available globally for Alpine.js integration
window.jspreadsheet = jspreadsheet;
