/**
 * Mortgage Calculator JavaScript
 * Handles mortgage calculation functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mortgage calculator
    initMortgageCalculator();
});

function initMortgageCalculator() {
    const calculateBtn = document.getElementById('calculate-mortgage-btn');
    const downPaymentSlider = document.getElementById('downPayment');
    const interestRateInput = document.getElementById('interestRate');
    const loanTermSelect = document.getElementById('loanTerm');
    const propertyPriceInput = document.getElementById('propertyPrice');
    
    if (!calculateBtn) return;
    
    // Add event listeners
    calculateBtn.addEventListener('click', calculateMortgage);
    
    if (downPaymentSlider) {
        downPaymentSlider.addEventListener('input', updateDownPaymentValue);
        downPaymentSlider.addEventListener('input', calculateMortgageNow);
    }
    
    if (interestRateInput) {
        interestRateInput.addEventListener('keyup', calculateMortgageNow);
        interestRateInput.addEventListener('change', calculateMortgageNow);
    }
    
    if (loanTermSelect) {
        loanTermSelect.addEventListener('change', calculateMortgageNow);
    }
    
    if (propertyPriceInput) {
        propertyPriceInput.addEventListener('keyup', calculateMortgageNow);
        propertyPriceInput.addEventListener('change', calculateMortgageNow);
    }
    
    // Initial calculation
    calculateMortgageNow();
}

function updateDownPaymentValue() {
    const downPaymentSlider = document.getElementById('downPayment');
    const downPaymentValue = document.getElementById('downPaymentValue');
    
    if (downPaymentSlider && downPaymentValue) {
        downPaymentValue.textContent = downPaymentSlider.value + '%';
    }
}

function calculateMortgage() {
    console.log('Button clicked!');
    
    // Get values
    const price = document.getElementById('propertyPrice').value;
    const downPayment = document.getElementById('downPayment').value;
    const interestRate = document.getElementById('interestRate').value;
    const loanTerm = document.getElementById('loanTerm').value;
    
    console.log('Input values:', {price: price, downPayment: downPayment, interestRate: interestRate, loanTerm: loanTerm});
    
    // Convert to numbers
    const priceNum = parseFloat(price) || 0;
    const downPaymentNum = parseFloat(downPayment) || 0;
    const interestRateNum = parseFloat(interestRate) || 0;
    const loanTermNum = parseFloat(loanTerm) || 30;
    
    // Handle very low property prices
    if (priceNum < 1000) {
        console.log('Property price too low for realistic calculation:', priceNum);
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }
    
    // Calculate
    const downPaymentAmount = (priceNum * downPaymentNum) / 100;
    const loanAmount = priceNum - downPaymentAmount;
    const monthlyRate = interestRateNum / 100 / 12;
    const numberOfPayments = loanTermNum * 12;
    
    let monthlyPayment = 0;
    if (monthlyRate > 0 && loanAmount > 0) {
        monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
    } else if (loanAmount > 0) {
        monthlyPayment = loanAmount / numberOfPayments;
    }
    
    console.log('Calculation result:', monthlyPayment);
    
    // Display result
    document.getElementById('monthlyPayment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
    console.log('Display updated to:', document.getElementById('monthlyPayment').textContent);
}

function calculateMortgageNow() {
    // Auto-calculate when inputs change
    const price = document.getElementById('propertyPrice');
    const downPayment = document.getElementById('downPayment');
    const interestRate = document.getElementById('interestRate');
    const loanTerm = document.getElementById('loanTerm');
    
    if (!price || !downPayment || !interestRate || !loanTerm) return;
    
    const priceNum = parseFloat(price.value) || 0;
    const downPaymentNum = parseFloat(downPayment.value) || 0;
    const interestRateNum = parseFloat(interestRate.value) || 0;
    const loanTermNum = parseFloat(loanTerm.value) || 30;
    
    // Only calculate if we have valid inputs
    if (priceNum > 0 && downPaymentNum >= 0 && interestRateNum >= 0 && loanTermNum > 0) {
        const downPaymentAmount = (priceNum * downPaymentNum) / 100;
        const loanAmount = priceNum - downPaymentAmount;
        const monthlyRate = interestRateNum / 100 / 12;
        const numberOfPayments = loanTermNum * 12;
        
        let monthlyPayment = 0;
        if (monthlyRate > 0 && loanAmount > 0) {
            monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
        } else if (loanAmount > 0) {
            monthlyPayment = loanAmount / numberOfPayments;
        }
        
        // Update down payment display
        updateDownPaymentValue();
        
        // Display result
        document.getElementById('monthlyPayment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
    }
}
