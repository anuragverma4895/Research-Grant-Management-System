// Function to validate the grant application form
function validateGrantApplication() {
    // Get form input values
    let researcherId = document.getElementById('researcher_id').value;
    let grantTitle = document.getElementById('grant_title').value;
    let grantDescription = document.getElementById('grant_description').value;
    let grantAmount = document.getElementById('grant_amount').value;
    let fundingAgencyId = document.getElementById('funding_agency_id').value;

    // Validate Researcher ID
    if (researcherId === "" || isNaN(researcherId)) {
        alert("Please enter a valid Researcher ID.");
        return false;
    }

    // Validate Grant Title
    if (grantTitle === "") {
        alert("Grant Title cannot be empty.");
        return false;
    }

    // Validate Grant Description
    if (grantDescription === "") {
        alert("Grant Description cannot be empty.");
        return false;
    }

    // Validate Grant Amount (should be a positive number)
    if (grantAmount === "" || isNaN(grantAmount) || grantAmount <= 0) {
        alert("Please enter a valid Grant Amount.");
        return false;
    }

    // Validate Funding Agency ID
    if (fundingAgencyId === "" || isNaN(fundingAgencyId)) {
        alert("Please enter a valid Funding Agency ID.");
        return false;
    }

    // If all fields are valid, return true to allow form submission
    return true;
}

// Attach form validation to the form submit event
document.addEventListener('DOMContentLoaded', function() {
    let form = document.getElementById('grant_application_form');
    if (form) {
        form.addEventListener('submit', function(event) {
            // Prevent form submission if validation fails
            if (!validateGrantApplication()) {
                event.preventDefault();
            }
        });
    }
});
