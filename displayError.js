// Function for errors relating to the member not being logged in.
function sessionTimeoutError() {
    alert("Your session has timed out, please sign in again.");
    window.location.href="http://localhost:8080/signIn.php";
}


// Function if there was a database error.
function databaseError() {
    alert("We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-222-2020.");
    // Go back a page.
    window.location = window.location.href;
}


// Member is already logged in and tries to access the logIn page again.
function alreadyLoggedInMsg() {
    alert("You are already logged in! Redirecting you to the members page.");
    window.location.href="http://localhost:8080/membersPage.php";
}


// Login failure (incorrect username, password, or user is not a member of TCI).
function loginFailedMsg() {
    alert("Invalid username/password combination (or user is not a member of TCI). Please try again.");
    window.location.href="http://localhost:8080/signIn.php";
}


// Successful Member Login message
function loginSuccessMsg() {
    alert("Login successful! Signing you in...");
    window.location.href="http://localhost:8080/membersPage.php";
}


// Successful Member Logout message
function logoutSuccessMsg() {
    alert("You have securely logged out! Have a nice day!");
    window.location.href="http://localhost:8080/index.php";
}


// Warns user that selected username has already been taken.
function usernameTakenMsg() {
    alert("Sorry, that username has already been taken! Please choose a different one.");
    window.location = window.location.href;
}


// Warns user if they try to change their password and the conformation doesn't match.
function unacceptibleNewPasswordMsg($link) {
    alert("Passwords either do not match! Please try again!");
    window.location.href=$link;

}


// Tells user to check their email and activate their account.
function emailAccountActivationMsg() {
    alert("Congratulations! You have created an account with TCI! An email has been sent to your email account. Please follow the link in the email to activate your account!");
    window.location.href="http://localhost:8080/index.php";
}

// Begins activating the users account should email service be unavailable (or be administratively set to "off"). Requires a link to be specified with function call.
function noEmailAccountActivationMsg($link) {
    alert("Activating your new TCI account...");
    window.location.href=$link;
}

// Warns user if an invalid activation token is used.
function invalidActivationTokenMsg() {
    alert("Invalid activation token used. Please try again. If problems still persist, please notify TCI at 651-222-2020.");
    window.location.href="http://localhost:8080/index.php";
}


// Tells user that their account has been successfully activated.
function activationSuccessfulMsg() {
    alert("Way to go! Your TCI account has been officially activated! Please sign in.");
    window.location.href="http://localhost:8080/signIn.php";
}


// Account deletion complete message (shown if user chooses to delete his/her account).
function accountDeletionCompletedMsg() {
    alert("Account has been deleted successfully!");
    window.location.href = "http://localhost:8080/bin/signOut.php";
}


// Warns user if no dates are provided when looking for a room.
function noBookingDatesProvidedMsg() {
    alert("Hold on there! We need to know what dates you plan on booking on before we can show you the available rooms.");
    window.location.href="http://localhost:8080/booking.php";
}


// Warns user if check-out date comes AFTER check-in date.
function outOfOrderBookingDatesMsg() {
    alert("Your ending reservation date must come AFTER the starting date!");
    window.location = window.location.href;
}


// Warns user if an invalid room type has been specified by the user (upon booking a room).
function invalidRoomTypeMsg() {
    alert("Invalid room type specified! Please select a room and try again.");
    window.location.href="http://localhost:8080/searchRooms.php";
}


// Warns the user that paying for a room might take some time to process.
function warnBeforeProcessingOrderMsg() {
    alert("Your request will now be processed. Do not leave this page! ");
    window.location.href="http://localhost:8080/processOrder.php";
}


// Warns user if their room has gotten snatched up by someone else before they could pay for the room.
function roomSnatchedMsg() {
    alert("Sorry! It appears that someone else has managed to snatch this room from you before you had a chance to place your order. Please choose another room and try again.");
    window.location.href="http://localhost:8080/searchRooms.php";
}


// Displays when user successfully books and pays for a room.
function bookingSuccessfulMsg() {
    alert("Your purchase has completed successfully! Thank you for choosing to stay at TCI!");
    window.location.href="http://localhost:8080/membersPage.php";
}


// When user successfully adds a new form of payment.
function newPaymentFormAcceptedMsg() {
    alert("New form of payment added successfully!");
    window.history.back();
}


// When user tries to change their password, but not for the account they are signed in as.
function changeUsernameVerificationFailedMsg() {
    alert("Please enter your account name in the first field (to verify)! ");
    window.location.href="http://localhost:8080/accountInformationPage.php";
}


// When the user tries to change their username but the new username and username confirmation field don't match.
function newUsernameConfrmationFailedMsg() {
    alert("New username fields do not match! Please ensure that you have entered your new username in correctly!");
    window.location.href="http://localhost:8080/accountInformationPage.php";
}


// Displays when user changes their username successfully.
function successfulUsernameChangeMsg() {
    alert("Username changed successfully! Please sign in again.");
    window.location.href="http://localhost:8080/bin/signOut.php";
}


// When user tries to change password but it is the same as previous one.
function newPasswordMatchesOldMsg() {
    alert("Your current password matches your new one! Please choose a different password.");
    window.location.href="http://localhost:8080/accountInformationPage.php";
}


// Displays when user changes their password successfully.
function passwordChangeSuccessfulMsg() {
    alert("Password changed successfully!");
    window.location.href="http://localhost:8080/membersPage.php";
}


// If that username doesn't exist (passwordReset.php).
function nonExistantUsernameMsg() {
    alert("No user with that username/email address exists!");
    window.location.href="http://localhost:8080/passwordReset.php";
}


// Successful password reset.
function passwordResetSuccessfulMsg() {
    alert("Password reset successfully! Please check your email for your new password!");
    window.location.href="http://localhost:8080/signIn.php";
}