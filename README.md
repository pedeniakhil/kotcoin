# Payment System with Code-Based Transactions

This is a secure payment system where users can send and receive money using generated codes.

## How It Works

### For Receivers (Getting Money):
1. Go to **Receive Money** page
2. Click **"Generate New Code"** to create a unique 6-digit code
3. Share this code with the person who wants to send you money
4. The code is valid for 1 hour
5. When someone sends money using your code, it appears in your **Pending Transactions**
6. Enter the 5-letter transaction code to claim the money
7. You'll be redirected to a receipt page showing all transaction details

### For Senders (Sending Money):
1. Go to **Send Money** page
2. Enter the 6-digit code provided by the receiver
3. Enter the amount you want to send
4. Click **"Send Money"**
5. The money is deducted from your account and a 5-letter transaction code is generated
6. Share this transaction code with the receiver so they can claim the money

### Receipt Page:
After a successful transaction, both sender and receiver are redirected to a receipt page showing:
- Transaction ID
- Amount
- Sender name
- Receiver name
- Transaction code
- Date and time
- Option to print the receipt

## Database Setup

Run the `setup_database.php` file in your browser to create the necessary database tables:
- `pending_codes` - stores generated codes
- `transactions` - stores all transactions
- `users` - stores user accounts

## Features

- ✅ Secure code-based transactions
- ✅ Real-time balance updates
- ✅ Auto-refresh on receive page (every 30 seconds)
- ✅ Beautiful modern UI with Tailwind CSS
- ✅ Print-friendly receipts
- ✅ Transaction history
- ✅ Code expiration (1 hour)
- ✅ Prevents self-sending
- ✅ Input validation and error handling

## Security Features

- Codes expire after 1 hour
- Unique transaction codes prevent duplicates
- SQL injection protection
- Session-based authentication
- Input sanitization

## Files

- `receive_money.php` - Generate codes and claim money
- `send_money.php` - Send money using receiver's code
- `receipt.php` - Display transaction receipt
- `setup_database.php` - Database setup script
- `database.sql` - Database schema
- Other existing files for login, dashboard, etc. 