<?php
//reports.php
require_once '../config/database.php';
session_start();

// Get current period (default to daily)
$period = $_GET['period'] ?? 'daily';
$custom_date = $_GET['date'] ?? date('Y-m-d');

// Helper function to get date range based on period
function getDateRange($period, $custom_date = null) {
    $date = $custom_date ?: date('Y-m-d');
    
    switch($period) {
        case 'daily':
            return [
                'start' => $date,
                'end' => $date,
                'group_by' => 'HOUR(SaleDate)',
                'label' => 'Hour'
            ];
        case 'weekly':
            $start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
            return [
                'start' => $start,
                'end' => $end,
                'group_by' => 'DAYNAME(SaleDate)',
                'label' => 'Day'
            ];
        case 'monthly':
            return [
                'start' => date('Y-m-01', strtotime($date)),
                'end' => date('Y-m-t', strtotime($date)),
                'group_by' => 'DAY(SaleDate)',
                'label' => 'Day'
            ];
        case 'yearly':
            return [
                'start' => date('Y-01-01', strtotime($date)),
                'end' => date('Y-12-31', strtotime($date)),
                'group_by' => 'MONTH(SaleDate)',
                'label' => 'Month'
            ];
    }
}

$dateRange = getDateRange($period, $custom_date);

// Get summary statistics
$summary_query = "
    SELECT 
        COUNT(*) as total_sales,
        COALESCE(SUM(FinalAmount), 0) as total_revenue,
        COALESCE(SUM(Discount), 0) as total_discount,
        COALESCE(SUM(Tax), 0) as total_tax,
        COALESCE(AVG(FinalAmount), 0) as avg_sale_amount,
        COUNT(DISTINCT CustomerName) as unique_customers
    FROM sales 
    WHERE DATE(SaleDate) BETWEEN ? AND ?
";
$summary_stmt = $pdo->prepare($summary_query);
$summary_stmt->execute([$dateRange['start'], $dateRange['end']]);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

// Get sales data for charts
$chart_query = "
    SELECT 
        {$dateRange['group_by']} as period_unit,
        COUNT(*) as sales_count,
        COALESCE(SUM(FinalAmount), 0) as revenue
    FROM sales 
    WHERE DATE(SaleDate) BETWEEN ? AND ?
    GROUP BY {$dateRange['group_by']}
    ORDER BY period_unit
";
$chart_stmt = $pdo->prepare($chart_query);
$chart_stmt->execute([$dateRange['start'], $dateRange['end']]);
$chart_data = $chart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment method breakdown
$payment_query = "
    SELECT 
        PaymentMethod,
        COUNT(*) as transaction_count,
        COALESCE(SUM(FinalAmount), 0) as total_amount
    FROM sales 
    WHERE DATE(SaleDate) BETWEEN ? AND ?
    GROUP BY PaymentMethod
";
$payment_stmt = $pdo->prepare($payment_query);
$payment_stmt->execute([$dateRange['start'], $dateRange['end']]);
$payment_data = $payment_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top selling products
$products_query = "
    SELECT 
        si.ItemName,
        SUM(si.Quantity) as total_quantity,
        COUNT(DISTINCT si.SaleID) as times_sold,
        COALESCE(SUM(si.TotalPrice), 0) as total_revenue
    FROM sales_items si
    JOIN sales s ON si.SaleID = s.SaleID
    WHERE DATE(s.SaleDate) BETWEEN ? AND ?
    GROUP BY si.ItemName
    ORDER BY total_quantity DESC
    LIMIT 10
";
$products_stmt = $pdo->prepare($products_query);
$products_stmt->execute([$dateRange['start'], $dateRange['end']]);
$top_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent high-value sales
$high_value_query = "
    SELECT 
        SaleID,
        SaleDate,
        CustomerName,
        FinalAmount,
        PaymentMethod
    FROM sales 
    WHERE DATE(SaleDate) BETWEEN ? AND ?
    ORDER BY FinalAmount DESC
    LIMIT 5
";
$high_value_stmt = $pdo->prepare($high_value_query);
$high_value_stmt->execute([$dateRange['start'], $dateRange['end']]);
$high_value_sales = $high_value_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Pharmacy Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px;
            padding: 30px;
        }

        .dashboard-header {
            background: var(--primary-gradient);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }

        .period-selector {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .period-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .period-btn {
            padding: 10px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: white;
            color: #495057;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .period-btn:hover, .period-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            border-radius: 20px;
            padding: 25px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .revenue-card { background: var(--success-gradient); }
        .sales-card { background: var(--primary-gradient); }
        .discount-card { background: var(--warning-gradient); }
        .customers-card { background: var(--info-gradient); }
        .avg-card { background: var(--dark-gradient); }
        .tax-card { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); }

        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .chart-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .table-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .table-modern {
            margin: 0;
        }

        .table-modern thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            font-weight: 600;
            color: #495057;
            padding: 15px;
        }

        .table-modern tbody tr {
            border: none;
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }

        .table-modern tbody td {
            border: none;
            padding: 15px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .period-buttons {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title">
                        <i class="fas fa-chart-bar"></i> Sales Reports
                    </h1>
                    <p class="mb-0 opacity-90">Comprehensive sales analytics and insights</p>
                </div>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Period Selector -->
        <div class="period-selector">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-calendar-alt text-primary"></i> Report Period
                </h5>
                <input type="date" class="form-control" style="max-width: 200px;" value="<?php echo $custom_date; ?>" onchange="changeDate(this.value)">
            </div>
            <div class="period-buttons">
                <a href="?period=daily&date=<?php echo $custom_date; ?>" class="period-btn <?php echo $period === 'daily' ? 'active' : ''; ?>">
                    <i class="fas fa-sun"></i> Daily
                </a>
                <a href="?period=weekly&date=<?php echo $custom_date; ?>" class="period-btn <?php echo $period === 'weekly' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-week"></i> Weekly
                </a>
                <a href="?period=monthly&date=<?php echo $custom_date; ?>" class="period-btn <?php echo $period === 'monthly' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> Monthly
                </a>
                <a href="?period=yearly&date=<?php echo $custom_date; ?>" class="period-btn <?php echo $period === 'yearly' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> Yearly
                </a>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card revenue-card">
                <i class="fas fa-rupee-sign stat-icon"></i>
                <div class="stat-value">₹<?php echo number_format($summary['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card sales-card">
                <i class="fas fa-shopping-cart stat-icon"></i>
                <div class="stat-value"><?php echo number_format($summary['total_sales']); ?></div>
                <div class="stat-label">Total Sales</div>
            </div>
            <div class="stat-card discount-card">
                <i class="fas fa-percent stat-icon"></i>
                <div class="stat-value">₹<?php echo number_format($summary['total_discount'], 2); ?></div>
                <div class="stat-label">Total Discounts</div>
            </div>
            <div class="stat-card customers-card">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-value"><?php echo number_format($summary['unique_customers']); ?></div>
                <div class="stat-label">Unique Customers</div>
            </div>
            <div class="stat-card avg-card">
                <i class="fas fa-chart-line stat-icon"></i>
                <div class="stat-value">₹<?php echo number_format($summary['avg_sale_amount'], 2); ?></div>
                <div class="stat-label">Average Sale</div>
            </div>
            <div class="stat-card tax-card">
                <i class="fas fa-receipt stat-icon"></i>
                <div class="stat-value">₹<?php echo number_format($summary['total_tax'], 2); ?></div>
                <div class="stat-label">Total Tax</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-8">
                <div class="chart-container">
                    <div class="chart-header">
                        <i class="fas fa-chart-area text-primary"></i>
                        Sales Trend - <?php echo ucfirst($period); ?>
                    </div>
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <i class="fas fa-chart-pie text-success"></i>
                        Payment Methods
                    </div>
                    <canvas id="paymentChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Tables Row -->
        <div class="row">
            <div class="col-md-8">
                <div class="table-container">
                    <div class="chart-header">
                        <i class="fas fa-pills text-warning"></i>
                        Top Selling Products
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Qty Sold</th>
                                    <th>Times Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_products)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No product sales found for this period</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($product['ItemName']); ?></strong></td>
                                    <td><span class="badge bg-primary"><?php echo $product['total_quantity']; ?></span></td>
                                    <td><?php echo $product['times_sold']; ?></td>
                                    <td class="text-success"><strong>₹<?php echo number_format($product['total_revenue'], 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="table-container">
                    <div class="chart-header">
                        <i class="fas fa-star text-info"></i>
                        High-Value Sales
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($high_value_sales)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No sales found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($high_value_sales as $sale): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($sale['SaleID'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($sale['CustomerName'] ?: 'Walk-in'); ?></div>
                                        <small class="text-muted"><?php echo date('d M', strtotime($sale['SaleDate'])); ?></small>
                                    </td>
                                    <td class="text-success"><strong>₹<?php echo number_format($sale['FinalAmount'], 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const chartData = <?php echo json_encode($chart_data); ?>;
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: chartData.map(item => item.period_unit),
                datasets: [{
                    label: 'Revenue (₹)',
                    data: chartData.map(item => item.revenue),
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Sales Count',
                    data: chartData.map(item => item.sales_count),
                    borderColor: 'rgba(17, 153, 142, 1)',
                    backgroundColor: 'rgba(17, 153, 142, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Sales Count'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // Payment Methods Pie Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentData = <?php echo json_encode($payment_data); ?>;
        
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: paymentData.map(item => item.PaymentMethod),
                datasets: [{
                    data: paymentData.map(item => item.total_amount),
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(17, 153, 142, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(255, 206, 84, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        function changeDate(date) {
            const currentPeriod = '<?php echo $period; ?>';
            window.location.href = `?period=${currentPeriod}&date=${date}`;
        }
    </script>
</body>
</html>