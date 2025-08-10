<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6ffed;
            color: #2d6a4f;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(45, 106, 79, 0.3);
            text-align: center;
            max-width: 400px;
        }
        h1 {
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        a.button {
            text-decoration: none;
            background-color: #2d6a4f;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        a.button:hover {
            background-color: #1b3b27;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Successful</h1>
        <p>Thank you for your payment. Your reservation is now confirmed.</p>
        <a href="{{ url('/') }}" class="button">Back to Home</a>
    </div>
</body>
</html>
