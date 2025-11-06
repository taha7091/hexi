<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { margin-top: 30px; }
        .filter-section, .report-section { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .filter-section h4, .report-section h4 { color: #0056b3; margin-bottom: 20px; }
        .form-label { font-weight: bold; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .report-table th, .report-table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        .report-table th { background-color: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Report Generator</h2>

        <!-- Filter Section -->
        <div class="filter-section shadow-sm">
            <h4>Report Filters</h4>
            <form id="reportFilterForm">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="startDate" class="form-label">Start Date:</label>
                        <input type="date" class="form-control" id="startDate" name="startDate">
                    </div>
                    <div class="col-md-4">
                        <label for="endDate" class="form-label">End Date:</label>
                        <input type="date" class="form-control" id="endDate" name="endDate">
                    </div>
                    <div class="col-md-4">
                        <label for="reportType" class="form-label">Report Type:</label>
                        <select class="form-select" id="reportType" name="reportType">
                            <option value="sales">Sales Report</option>
                            <option value="inventory">Inventory Report</option>
                            <option value="customers">Customer Report</option>
                            <!-- Add more report types as needed -->
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all">All</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="product" class="form-label">Product:</label>
                        <input type="text" class="form-control" id="product" name="product" placeholder="Enter product name or ID">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <button type="button" class="btn btn-secondary" id="resetFilters">Reset Filters</button>
            </form>
        </div>

        <!-- Report Display Section -->
        <div class="report-section shadow-sm">
            <h4>Generated Report</h4>
            <div id="reportContent">
                <p class="text-muted">Select filters and click 'Generate Report' to see results.</p>
                <!-- Report data will be loaded here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('reportFilterForm').addEventListener('submit', function(event) {
            event.preventDefault();
            // In a real application, you would send an AJAX request here
            // to fetch report data based on the selected filters.
            const formData = new FormData(this);
            const filters = Object.fromEntries(formData.entries());
            console.log('Generating report with filters:', filters);

            // Simulate fetching data
            setTimeout(() => {
                const reportType = filters.reportType;
                let reportHtml = '';

                if (reportType === 'sales') {
                    reportHtml = `
                        <h5>Sales Report (Simulated)</h5>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>2023-10-26</td><td>TXN001</td><td>Laptop</td><td>1</td><td>$1200.00</td></tr>
                                <tr><td>2023-10-26</td><td>TXN002</td><td>Mouse</td><td>2</td><td>$50.00</td></tr>
                                <tr><td>2023-10-25</td><td>TXN003</td><td>Keyboard</td><td>1</td><td>$75.00</td></tr>
                            </tbody>
                        </table>
                    `;
                } else if (reportType === 'inventory') {
                    reportHtml = `
                        <h5>Inventory Report (Simulated)</h5>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Stock</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>PROD001</td><td>Laptop</td><td>50</td><td>$1150.00</td></tr>
                                <tr><td>PROD002</td><td>Mouse</td><td>200</td><td>$20.00</td></tr>
                                <tr><td>PROD003</td><td>Keyboard</td><td>100</td><td>$60.00</td></tr>
                            </tbody>
                        </table>
                    `;
                } else if (reportType === 'customers') {
                    reportHtml = `
                        <h5>Customer Report (Simulated)</h5>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Total Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>CUST001</td><td>John Doe</td><td>john.doe@example.com</td><td>5</td></tr>
                                <tr><td>CUST002</td><td>Jane Smith</td><td>jane.smith@example.com</td><td>3</td></tr>
                            </tbody>
                        </table>
                    `;
                }

                document.getElementById('reportContent').innerHTML = reportHtml;
            }, 500);
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('reportFilterForm').reset();
            document.getElementById('reportContent').innerHTML = '<p class="text-muted">Select filters and click \'Generate Report\' to see results.</p>';
        });
    </script>
</body>
</html>
