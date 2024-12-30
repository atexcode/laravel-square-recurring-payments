<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Square Customer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script type="text/javascript" src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
    <style>
        #card-container {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Create Square Customer</h2>
        <form action="{{ route('square.customer.store') }}" method="POST" id="payment-form">
            @csrf
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <h3>Card Information</h3>
            <div id="card-container"></div> <!-- Square injects the card input field here -->
            <div id="payment-status" class="text-danger mt-2"></div>

            <input type="hidden" id="nonce" name="nonce" />
            <button type="submit" class="btn btn-primary mt-3" id="submit-button">Create Customer</button>
        </form>
    </div>

    <script>
        // Load Square Web Payments SDK
        async function initializeSquare() {
            const payments = Square.payments('sandbox-sq0idb-i-9FsTa9F-5q7VRc9cFmjA', 'L12VC933ACRZP'); // Replace with your Application ID and Location ID
            const card = await payments.card();
            await card.attach('#card-container');

            // Handle form submission
            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-button');
            const statusContainer = document.getElementById('payment-status');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                try {
                    // Request card nonce
                    const result = await card.tokenize();
                    if (result.status === 'OK') {
                        document.getElementById('nonce').value = result.token;
                        form.submit(); // Submit the form after nonce is received
                    } else {
                        throw new Error(result.errors.map(error => error.message).join(', '));
                    }
                } catch (error) {
                    statusContainer.textContent = error.message;
                }
            });
        }

        // Initialize the Square Web Payments SDK
        document.addEventListener('DOMContentLoaded', initializeSquare);
    </script>
</body>
</html>
