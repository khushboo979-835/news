<?php
// Migration disabled to protect all live news database records.
http_response_code(403);
die("Access forbidden: Migration is permanently disabled to protect database records.");
