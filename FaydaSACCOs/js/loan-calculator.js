document.getElementById('loanCalculator').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get input values
    const amount = parseFloat(document.getElementById('loanAmount').value);
    const interest = parseFloat(document.getElementById('interestRate').value) / 100 / 12;
    const term = parseInt(document.getElementById('loanTerm').value);
    
    // Validate inputs
    if (isNaN(amount) || isNaN(interest) || isNaN(term) || amount <= 0 || term <= 0) {
        alert('Please enter valid values for all fields');
        return;
    }
    
    // Calculate monthly payment
    const x = Math.pow(1 + interest, term);
    const monthly = (amount * x * interest) / (x - 1);
    
    // Calculate total interest
    const totalInterest = (monthly * term) - amount;
    
    // Display results
    document.getElementById('monthlyPayment').textContent = monthly.toFixed(2);
    document.getElementById('totalInterest').textContent = totalInterest.toFixed(2);
    document.getElementById('loanResult').style.display = 'block';
    
    // Scroll to results
    document.getElementById('loanResult').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});