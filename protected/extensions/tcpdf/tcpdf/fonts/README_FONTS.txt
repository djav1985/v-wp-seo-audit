# Font Files

The compressed .z font files have been removed because they were corrupted and causing
"gzuncompress(): data error" errors during PDF generation.

TCPDF will use the .php font definition files instead, which work correctly without
the compressed data files. This is a supported configuration in TCPDF.

If you need to regenerate compressed font files:
1. Use the TCPDF font tools (tcpdf_addfont.php utility)
2. Or download fresh TCPDF package from https://github.com/tecnickcom/TCPDF

Note: The .php files contain all necessary font metrics and character mappings.
The .z files are optional compressed versions for faster loading (at the cost of
needing gzuncompress support).

Date: 2025-10-14
Issue: PDF download was failing with HTTP 500 error due to corrupted .z files
Solution: Removed all .z compressed font files; TCPDF uses .php definitions instead
