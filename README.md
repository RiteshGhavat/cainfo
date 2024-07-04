Directory Structure
Ensure your project directory structure is as follows:

arduinoyour_project/
├── config.php
├── index.php
├── Parsedown.php
├── assest/
│   └── logo2.png
└── uploads/

Explanation:
Edit README.md: Add a link to your website in the README.md file on GitHub.
PHP Code:
Use file_get_contents to fetch the raw content of the README.md file.
Convert the Markdown content to HTML using the Parsedown library.
Embed the converted HTML content in your existing HTML structure.
This ensures that the link to https://cainfo.great-site.net/ is included in the content fetched from the README.md file and displayed on your site. Make sure to replace the URL in file_get_contents with the actual URL of your Markdown file on GitHub.
