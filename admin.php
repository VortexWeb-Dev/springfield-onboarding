<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PDF Upload App</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet" />
  <style>
    #pdfViewer {
      height: calc(100vh - 50px);
      width: calc(100vw - 150px);
      overflow: auto;
    }
  </style>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="container mx-auto my-10">
    <h1 class="text-3xl font-bold text-center">SpringField</h1>
    <h2 class="text-xl font-semibold mb-10 text-center">Onboarding</h2>

    <button type="button" id="add-card" class="p-4 bg-white border shadow-sm rounded-xl">
      Add PDF
    </button>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 mt-4">
      <thead>
        <tr>
          <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500" class="w-1/3">Name</th>
          <!-- <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500" class="w-1/3">Description</th> -->
          <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500" class="w-1/3">Date</th>
          <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500" class="w-1/3">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">

        <?php
        function formatDate($dateString)
        {
          $date = new DateTime($dateString);
          return $date->format('jS M, Y');
        }

        require_once './config/db.php';
        $conn = getDatabaseConnection();
        $query = "SELECT pdf_name, description, pdf_data, created_at FROM pdf";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $pdf_name = htmlspecialchars($row['pdf_name']);
            // $description = htmlspecialchars($row['description']);
            $created_at = formatDate(htmlspecialchars($row['created_at']));
            $pdf_data = base64_encode($row['pdf_data']);

            echo "
                <tr>
                    <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200'>$pdf_name</td>
                    <!-- <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200'>$description</td> -->
                    <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200'>$created_at</td>
                    <td class='px-6 py-4 whitespace-nowrap text-end text-sm font-medium'>
                        <button type='button' class='inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 focus:outline-none focus:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 dark:focus:text-blue-400 open-pdf' data-pdf='$pdf_data'>
                            Open
                        </button>
                        <button type='button' class='inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:text-red-800 focus:outline-none focus:text-red-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 dark:focus:text-blue-400' data-pdf='$pdf_data'>
                            Delete
                        </button>
                    </td>
                </tr>
            ";
          }
        } else {
          echo "<p class='text-center text-gray-500'>Start uploading your PDF.</p>";
        }

        $conn->close();
        ?>
      </tbody>

    </table>
  </div>
  <!-- <img class='mt-4 rounded' src='./test.jpg' width='100%' height='200px' /> -->
  <!-- <img class='mt-4 rounded' src='./test.jpg' width='100%' height='200px' /> -->
  <!-- Upload Modal -->
  <div id="uploadModal" class="fixed inset-0 bg-gray-800 bg-opacity-70 hidden z-50 flex items-center justify-center">
    <form class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md" enctype="multipart/form-data" method="POST" action="./actions/upload_pdf.php">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Upload PDF</h2>

      <input type="text" name="pdf_name" class="w-full mb-4 p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Enter PDF name" required />

      <!-- <textarea name="description" class="w-full mb-4 p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Enter PDF description" rows="3"></textarea> -->

      <input type="file" id="pdf_file" accept="application/pdf" class="w-full p-2 mb-4 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" name="pdf_file" required />

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
      <button id="closePdfModal" class="absolute top- -left-10 bg-white p-2 rounded-tl-full rounded-bl-full focus:ring-2 focus:ring-blue-500 focus:outline-none ">
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
    document.getElementById('add-card').addEventListener('click', () => {
      document.getElementById('uploadModal').classList.remove('hidden');
    });

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
              const scale = 1.5;
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
      document.getElementById('pdfViewer').innerHTML = ''; // Clear viewer
    });

    // Close Upload Modal
    document.getElementById('closeModal').addEventListener('click', () => {
      document.getElementById('uploadModal').classList.add('hidden');
    });
  </script>
</body>

</html>