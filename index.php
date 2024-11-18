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
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="text-gray-800">
  <div class="container mx-auto my-10">
    <h1 class="text-4xl font-bold mb-10 text-center text-black">Onboarding</h1>

    <!-- Add PDF Button -->
    <button style="background-color: #003172;" id="add-pdf-button" class="absolute top-5 right-5 text-white py-2 px-4 rounded transition duration-200 ease-in-out hover:bg-blue-200">
      Add PDF
    </button>

    <div id="grid" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <?php
      function formatDate($dateString)
      {
        $date = new DateTime($dateString);
        return $date->format('jS M, Y');
      }

      require_once './config/db.php';
      $conn = getDatabaseConnection();
      $query = "SELECT * FROM pdf";
      $result = $conn->query($query);

      if ($result && $result->num_rows > 0) {
        $count = 0;
        while ($row = $result->fetch_assoc()) {
          if ($count >= 3) {
            break;
          }

          $pdf_name = htmlspecialchars($row['pdf_name']);
          $thumbnail_type = htmlspecialchars($row['thumbnail_type']);
          $created_at = formatDate(htmlspecialchars($row['created_at']));
          $pdf_data = base64_encode($row['pdf_data']);
          $thumbnail_data = base64_encode($row['thumbnail_data']);

          echo "
              <div class='flex flex-col bg-white border shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-900 dark:border-neutral-700 dark:shadow-neutral-700/70 cursor-pointer open-pdf hover:shadow-lg' data-pdf='$pdf_data'>
                <div class='flex justify-between items-center'>
                  <div>
                    <h3 class='text-lg font-bold text-gray-800 dark:text-white'>$pdf_name</h3>
                    <p class='mt-1 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 lowercase'>$created_at</p>
                  </div>
                  <button onclick=\"downloadPDF('$pdf_name', '$pdf_data')\" class='mt-4 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded transition duration-200 ease-in-out'>
                    <i class='fa-solid fa-download'></i>
                  </button>
                </div>

                  <img class='mt-4 rounded object-cover object-center max-w-96 h-48' src='data:$thumbnail_type;base64,$thumbnail_data' width='100%' height='200px' alt='thumbnail' />

                  
              </div>
          ";


          $count++; // Increment the counter
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

      <!-- PDF Name Input -->
      <div class="mb-4">
        <label for="pdf_name" class="block text-sm font-medium text-gray-700 mb-2">PDF Name</label>
        <input type="text" id="pdf_name" name="pdf_name" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Enter PDF name" required />
      </div>

      <!-- PDF File Input -->
      <div class="mb-4">
        <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">Upload PDF File</label>
        <input type="file" id="pdf_file" accept="application/pdf" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" name="pdf_file" required />
        <p class="text-xs text-gray-500 mt-1">Max file size: 5MB</p>
      </div>

      <!-- Thumbnail File Input -->
      <div class="mb-4">
        <label for="thumbnail_file" class="block text-sm font-medium text-gray-700 mb-2">Upload Thumbnail Image</label>
        <input type="file" id="thumbnail_file" accept="image/*" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" name="thumbnail_file" required />
        <p class="text-xs text-gray-500 mt-1">Max file size: 5MB</p>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end">
        <button id="closeModal" type="button" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2 transition duration-200 ease-in-out">
          Cancel
        </button>
        <button type="submit" id="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-200 ease-in-out">Upload</button>
      </div>
    </form>
  </div>

  <!-- PDF Viewer Modal -->
  <div id="pdfModal" class="fixed h-screen w-screen inset-0 bg-gray-800 bg-opacity-50 hidden z-50 flex items-center justify-end">
    <div class="relative bg-white p-6 ">
      <!-- Close Button Positioned Outside -->
      <button id="closePdfModal" class="absolute top-2 -left-10 bg-blue-300 p-2 rounded-tl-full rounded-bl-full focus:ring-2 focus:ring-blue-500 focus:outline-none ">
        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M19.207 6.207a1 1 0 0 0-1.414-1.414L12 10.586 6.207 4.793a1 1 0 0 0-1.414 1.414L10.586 12l-5.793 5.793a1 1 0 1 0 1.414 1.414L12 13.414l5.793 5.793a1 1 0 0 0 1.414-1.414L13.414 12l5.793-5.793z" fill="#000000" />
        </svg>
      </button>

      <!-- Modal Content -->
      <div id="pdfViewer">Loading...</div>
    </div>
  </div>

  <script>
    // Open Upload Modal
    document.getElementById('add-pdf-button').addEventListener('click', () => {
      document.getElementById('uploadModal').classList.remove('hidden');
    });
    pdfViewer
    // Open PDF modal and render PDF
    document.querySelectorAll('.open-pdf').forEach(button => {
      button.addEventListener('click', () => {
        const pdfData = button.getAttribute('data-pdf');
        const pdfViewer = document.getElementById('pdfViewer');
        const loadingTask = pdfjsLib.getDocument({
          data: atob(pdfData)
        }); // Decode base64

        loadingTask.promise.then(pdf => {
          pdfViewer.innerHTML = ''; // Clear previous content
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
      });
    });

    // Close PDF modal
    document.getElementById('closePdfModal').addEventListener('click', () => {
      document.getElementById('pdfModal').classList.add('hidden');
      document.getElementById('pdfViewer').innerHTML = 'Loading...'; // Reset content
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

    function downloadPDF(pdfName, pdfData) {
      const link = document.createElement('a');
      link.href = `data:application/pdf;base64,${pdfData}`;
      link.download = `${pdfName}.pdf`; // Sets the download file name
      link.click(); // Triggers the download
    }
  </script>
</body>

</html>
