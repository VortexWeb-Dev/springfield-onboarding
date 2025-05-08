<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Springfield Onboarding</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #2FAFE5;
    }

    #pdfViewer {
      height: calc(100vh - 50px);
      width: calc(100vw - 100px);
      overflow: auto;
    }

    .card {
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
    }

    .thumbnail {
      max-height: 200px;
      object-fit: cover;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Bitrix JS SDK -->
  <script src="https://api.bitrix24.com/api/v1/"></script>
</head>

<body class="text-gray-800">
  <div class="container mx-auto my-10">
    <h1 class="text-4xl font-bold mb-10 text-center text-black">Onboarding</h1>

    <!-- Add PDF Button -->
    <button style="background-color: #003172;" id="add-pdf-button" class="absolute top-5 right-5 text-white py-2 px-4 rounded transition duration-200 ease-in-out hover:bg-blue-200">
      Add PDF
    </button>

    <div id="grid" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      function formatDate($dateString)
      {
        $date = new DateTime($dateString);
        return $date->format('jS M, Y');
      }

      require_once './config/db.php';
      $conn = getDatabaseConnection();
      $query = "SELECT id, pdf_name, thumbnail_type, created_at, pdf_data, thumbnail_data FROM pdf";
      $result = $conn->query($query);

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $pdf_id = htmlspecialchars($row['id']);
          $pdf_name = htmlspecialchars($row['pdf_name']);
          $thumbnail_type = htmlspecialchars($row['thumbnail_type']);
          $created_at = formatDate(htmlspecialchars($row['created_at']));
          $pdf_data = base64_encode($row['pdf_data']);
          $thumbnail_data = base64_encode($row['thumbnail_data']);

          echo "
              <div class='card flex flex-col bg-white border rounded-xl p-6 dark:bg-neutral-900 dark:border-neutral-700 cursor-pointer open-pdf' data-pdf='$pdf_data' data-id='$pdf_id'>
                <div class='flex justify-between items-center mb-4'>
                  <div>
                    <h3 class='text-lg font-bold text-gray-800 dark:text-white'>$pdf_name</h3>
                    <p class='mt-1 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 lowercase'>$created_at</p>
                  </div>
                  <div class='flex space-x-2'>
                    <button onclick=\"downloadPDF('$pdf_name', '$pdf_data')\" class='bg-gray-600 hover:bg-gray-700 text-white py-1 px-3 rounded transition duration-200'>
                      <i class='fa-solid fa-download'></i>
                    </button>
                    <button class='delete-pdf hidden bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded transition duration-200' data-id='$pdf_id'>
                      <i class='fa-solid fa-trash'></i>
                    </button>
                  </div>
                </div>
                <img class='thumbnail rounded w-full lazy' data-src='data:$thumbnail_type;base64,$thumbnail_data' alt='thumbnail' />
              </div>
          ";
        }
      } else {
        echo "<p class='text-center text-gray-500'>Start uploading your PDF.</p>";
      }

      $conn->close();
      ?>
    </div>
  </div>

  <!-- Upload Modal -->
  <div id="uploadModal" class="fixed inset-0 bg-gray-800 bg-opacity-70 hidden z-50 flex items-center justify-center">
    <form id="uploadForm" class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md" enctype="multipart/form-data" method="POST" action="./actions/upload_pdf.php" onsubmit="return validateFileSize()">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Upload PDF</h2>
      <div class="mb-4">
        <label for="pdf_name" class="block text-sm font-medium text-gray-700 mb-2">PDF Name</label>
        <input type="text" id="pdf_name" name="pdf_name" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Enter PDF name" required />
      </div>
      <div class="mb-4">
        <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">Upload PDF File</label>
        <input type="file" id="pdf_file" accept="application/pdf" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" name="pdf_file" required />
        <p class="text-xs text-gray-500 mt-1">Max file size: 5MB</p>
      </div>
      <div class="mb-4">
        <label for="thumbnail_file" class="block text-sm font-medium text-gray-700 mb-2">Upload Thumbnail Image</label>
        <input type="file" id="thumbnail_file" accept="image/*" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" name="thumbnail_file" required />
        <p class="text-xs text-gray-500 mt-1">Max file size: 5MB</p>
      </div>
      <div class="flex justify-end">
        <button id="closeModal" type="button" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2 transition duration-200 ease-in-out">
          Cancel
        </button>
        <button type="submit" id="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-200 ease-in-out">Upload</button>
      </div>
    </form>
  </div>

  <!-- PDF Viewer Modal (Unchanged) -->
  <div id="pdfModal" class="fixed h-screen w-screen inset-0 bg-gray-800 bg-opacity-50 hidden z-50 flex items-center justify-end">
    <div class="relative bg-white p-6 ">
      <button id="closePdfModal" class="absolute top-2 -left-10 bg-blue-300 p-2 rounded-tl-full rounded-bl-full focus:ring-2 focus:ring-blue-500 focus:outline-none ">
        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M19.207 6.207a1 1 0 0 0-1.414-1.414L12 10.586 6.207 4.793a1 1 0 0 0-1.414 1.414L10.586 12l-5.793 5.793a1 1 0 1 0 1.414 1.414L12 13.414l5.793 5.793a1 1 0 0 0 1.414-1.414L13.414 12l5.793-5.793z" fill="#000000" />
        </svg>
      </button>
      <div id="pdfViewer">Loading...</div>
    </div>
  </div>

  <script>
    // Lazy Loading with Intersection Observer
    const lazyImages = document.querySelectorAll('img.lazy');
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove('lazy');
          observer.unobserve(img);
        }
      });
    });
    lazyImages.forEach(img => observer.observe(img));

    // Debounce Function
    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    // Open Upload Modal
    document.getElementById('add-pdf-button').addEventListener('click', () => {
      document.getElementById('uploadModal').classList.remove('hidden');
    });

    // Open PDF modal and render PDF
    const openPDF = debounce((button) => {
      const pdfData = button.getAttribute('data-pdf');
      const pdfViewer = document.getElementById('pdfViewer');
      const loadingTask = pdfjsLib.getDocument({
        data: atob(pdfData)
      });

      loadingTask.promise.then(pdf => {
        pdfViewer.innerHTML = '';
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
          pdf.getPage(pageNum).then(page => {
            const scale = 0.8;
            const viewport = page.getViewport({
              scale: scale
            });
            const canvas = document.createElement('canvas');
            canvas.style.margin = '0 auto';
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            pdfViewer.appendChild(canvas);

            const renderContext = {
              canvasContext: context,
              viewport: viewport
            };
            page.render(renderContext);
          });
        }
      }).catch(error => {
        console.error('Error loading PDF: ', error);
      });

      document.getElementById('pdfModal').classList.remove('hidden');
    }, 300);

    document.querySelectorAll('.open-pdf').forEach(button => {
      button.addEventListener('click', (e) => {
        if (!e.target.closest('.delete-pdf, .fa-download')) {
          openPDF(button);
        }
      });
    });

    // Close PDF modal
    document.getElementById('closePdfModal').addEventListener('click', () => {
      document.getElementById('pdfModal').classList.add('hidden');
      document.getElementById('pdfViewer').innerHTML = 'Loading...';
    });

    // Close Upload modal
    document.getElementById('closeModal').addEventListener('click', () => {
      document.getElementById('uploadModal').classList.add('hidden');
    });

    // Validate file size
    function validateFileSize() {
      const pdfFile = document.getElementById('pdf_file').files[0];
      const thumbnailFile = document.getElementById('thumbnail_file').files[0];

      if (pdfFile.size > 5 * 1024 * 1024) {
        alert("PDF file size exceeds 5MB.");
        return false;
      }

      if (thumbnailFile.size > 5 * 1024 * 1024) {
        alert("Thumbnail file size exceeds 5MB.");
        return false;
      }

      return true;
    }

    // Download PDF
    function downloadPDF(pdfName, pdfData) {
      const link = document.createElement('a');
      link.href = `data:application/pdf;base64,${pdfData}`;
      link.download = `${pdfName}.pdf`;
      link.click();
    }

    // Bitrix SDK: Get Current User ID and Show Delete Button
    BX24.init(() => {
      BX24.callMethod('user.current', {}, (result) => {
        if (result.error()) {
          console.error('Error fetching user:', result.error());
          return;
        }
        const userId = result.data().ID;
        const allowedUserIds = [8, 267, 1, 289];
        if (allowedUserIds.includes(parseInt(userId))) {
          document.querySelectorAll('.delete-pdf').forEach(button => {
            button.classList.remove('hidden');
            button.addEventListener('click', () => {
              const pdfId = button.getAttribute('data-id');
              if (confirm('Are you sure you want to delete this PDF?')) {
                fetch('./actions/delete_pdf.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${pdfId}`
                  })
                  .then(response => response.json())
                  .then(data => {
                    if (data.success) {
                      button.closest('.card').remove();
                      alert('PDF deleted successfully.');
                    } else {
                      alert('Error deleting PDF: ' + data.message);
                    }
                  })
                  .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting PDF.');
                  });
              }
            });
          });
        }
      });
    });
  </script>
</body>

</html>