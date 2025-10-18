<?php
include '../../config/config.php';
session_start();
include DIR_URL.'src/global/middleware.php';
$userId = $_SESSION['userId'];
$userType = $_SESSION['userType'];

if ($userType != 'Admin') {
    http_response_code(403);
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>403 Forbidden</title>
        <style>
            html, body {
                margin: 0;
                padding: 0;
                height: 100%;
                width: 100%;
                background: url("../../assets/images/http403.jpg") no-repeat center center;
                background-size: contain;
                background-color: black;
            }
        </style>
    </head>
    <body>
    </body>
    </html>';
    exit();
}

include DIR_URL.'config/dbConnection.php';

// Get date range for filtering
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Overall statistics
$overallSql = "SELECT 
    COUNT(*) as total_books,
    SUM(available) as total_available,
    SUM(borrowed) as total_borrowed,
    SUM(totalCopies) as total_copies
    FROM books";
$overallResult = $conn->query($overallSql);
$overallStats = $overallResult->fetch_assoc();

// New arrivals in date range
$newArrivalsSql = "SELECT 
    bs.isbn, b.bookName, b.authorName, bs.date_added, bs.new_arrivals
    FROM book_statistics bs
    JOIN books b ON bs.isbn = b.isbn
    WHERE bs.date_added BETWEEN '$startDate' AND '$endDate'
    ORDER BY bs.date_added DESC";
$newArrivalsResult = $conn->query($newArrivalsSql);
$newArrivals = [];
if ($newArrivalsResult->num_rows > 0) {
    while ($row = $newArrivalsResult->fetch_assoc()) {
        $newArrivals[] = $row;
    }
}

// Borrowed books in date range
$borrowedSql = "SELECT 
    t.isbn, b.bookName, b.authorName, t.borrowDate, u.userId, u.userType
    FROM transactions t
    JOIN books b ON t.isbn = b.isbn
    JOIN users u ON t.userId = u.userId
    WHERE t.borrowDate BETWEEN '$startDate' AND '$endDate'
    ORDER BY t.borrowDate DESC";
$borrowedResult = $conn->query($borrowedSql);
$borrowedBooks = [];
if ($borrowedResult->num_rows > 0) {
    while ($row = $borrowedResult->fetch_assoc()) {
        $borrowedBooks[] = $row;
    }
}

// Returned books in date range
$returnedSql = "SELECT 
    t.isbn, b.bookName, b.authorName, t.returnDate, u.userId, u.userType
    FROM transactions t
    JOIN books b ON t.isbn = b.isbn
    JOIN users u ON t.userId = u.userId
    WHERE t.returnDate BETWEEN '$startDate' AND '$endDate'
    ORDER BY t.returnDate DESC";
$returnedResult = $conn->query($returnedSql);
$returnedBooks = [];
if ($returnedResult->num_rows > 0) {
    while ($row = $returnedResult->fetch_assoc()) {
        $returnedBooks[] = $row;
    }
}

// Monthly trends
$monthlySql = "SELECT 
    DATE_FORMAT(bs.date_added, '%Y-%m') as month,
    SUM(bs.new_arrivals) as new_books,
    SUM(bs.total_borrowed) as borrowed_count,
    SUM(bs.total_returned) as returned_count
    FROM book_statistics bs
    WHERE bs.date_added >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(bs.date_added, '%Y-%m')
    ORDER BY month DESC";
$monthlyResult = $conn->query($monthlySql);
$monthlyData = [];
if ($monthlyResult->num_rows > 0) {
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthlyData[] = $row;
    }
}

// Category statistics
$categorySql = "SELECT 
    category,
    COUNT(*) as book_count,
    SUM(available) as available_count,
    SUM(borrowed) as borrowed_count
    FROM books 
    WHERE category IS NOT NULL AND category != ''
    GROUP BY category
    ORDER BY book_count DESC";
$categoryResult = $conn->query($categorySql);
$categoryStats = [];
if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categoryStats[] = $row;
    }
}

// Calculate totals for the date range
$totalNewArrivals = array_sum(array_column($newArrivals, 'new_arrivals'));
$totalBorrowed = count($borrowedBooks);
$totalReturned = count($returnedBooks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Statistics</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/dashboard.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .date-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .date-filter form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .date-filter input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .date-filter button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .summary-card h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .summary-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="background-container"></div>
    
    <div class="container">
        <h1><i class="fas fa-chart-bar"></i> Library Statistics Dashboard</h1>
        
        <!-- Date Filter -->
        <div class="date-filter">
            <form method="GET">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>" required>
                
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>" required>
                
                <button type="submit">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>
        </div>
        
        <!-- Overall Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo $overallStats['total_books']; ?></h3>
                <p>Total Books in Library</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $overallStats['total_available']; ?></h3>
                <p>Available Books</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $overallStats['total_borrowed']; ?></h3>
                <p>Currently Borrowed</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $overallStats['total_copies']; ?></h3>
                <p>Total Copies</p>
            </div>
        </div>
        
        <!-- Period Summary -->
        <div class="summary-cards">
            <div class="summary-card">
                <h4>New Arrivals</h4>
                <div class="number"><?php echo $totalNewArrivals; ?></div>
                <p>Books added in period</p>
            </div>
            <div class="summary-card">
                <h4>Books Borrowed</h4>
                <div class="number"><?php echo $totalBorrowed; ?></div>
                <p>Books borrowed in period</p>
            </div>
            <div class="summary-card">
                <h4>Books Returned</h4>
                <div class="number"><?php echo $totalReturned; ?></div>
                <p>Books returned in period</p>
            </div>
        </div>
        
        <!-- Monthly Trends Chart -->
        <div class="section">
            <h2><i class="fas fa-chart-line"></i> Monthly Trends</h2>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <!-- Category Statistics -->
        <div class="section">
            <h2><i class="fas fa-tags"></i> Books by Category</h2>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        
        <!-- New Arrivals -->
        <div class="section">
            <h2><i class="fas fa-book-medical"></i> New Arrivals (<?php echo date('M d, Y', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($endDate)); ?>)</h2>
            <div class="table-container">
                <?php if (empty($newArrivals)): ?>
                <div class="no-data">No new books added in this period.</div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Book Name</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Date Added</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newArrivals as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['bookName']); ?></td>
                            <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($book['date_added'])); ?></td>
                            <td><?php echo $book['new_arrivals']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recently Borrowed Books -->
        <div class="section">
            <h2><i class="fas fa-hand-holding"></i> Recently Borrowed Books</h2>
            <div class="table-container">
                <?php if (empty($borrowedBooks)): ?>
                <div class="no-data">No books borrowed in this period.</div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Book Name</th>
                            <th>Author</th>
                            <th>Borrower</th>
                            <th>User Type</th>
                            <th>Borrow Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowedBooks as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['bookName']); ?></td>
                            <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                            <td><?php echo htmlspecialchars($book['userId']); ?></td>
                            <td><?php echo htmlspecialchars($book['userType']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($book['borrowDate'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recently Returned Books -->
        <div class="section">
            <h2><i class="fas fa-undo"></i> Recently Returned Books</h2>
            <div class="table-container">
                <?php if (empty($returnedBooks)): ?>
                <div class="no-data">No books returned in this period.</div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Book Name</th>
                            <th>Author</th>
                            <th>Returner</th>
                            <th>User Type</th>
                            <th>Return Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($returnedBooks as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['bookName']); ?></td>
                            <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                            <td><?php echo htmlspecialchars($book['userId']); ?></td>
                            <td><?php echo htmlspecialchars($book['userType']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($book['returnDate'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'New Books',
                    data: monthlyData.map(item => item.new_books),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Borrowed Books',
                    data: monthlyData.map(item => item.borrowed_count),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Returned Books',
                    data: monthlyData.map(item => item.returned_count),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryData = <?php echo json_encode($categoryStats); ?>;
        
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.book_count),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#FF6384',
                        '#C9CBCF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>



