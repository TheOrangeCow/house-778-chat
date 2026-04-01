<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    .custom-alert {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        display: none;
        max-width: 400px;
        width: 90%; 
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 0; 
    }
    
    .custom-alert.show {
        display: block;
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    
    .custom-alert h2 {
        text-align: center;
        color: #333333;
        margin-bottom: 15px;
        font-size: 1.5rem; 
    }
    
    .custom-alert p {
        color: #555555; 
        font-size: 1rem;
        margin-bottom: 20px;
        text-align: center;
        line-height: 1.4;
    }
    
    .custom-alert button {
        padding: 10px 25px; 
        background-color: #007bff;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: block;
        margin: 0 auto;
        font-size: 1rem; 
        transition: background-color 0.3s ease;
    }
    
    .custom-alert button:hover {
        background-color: #0056b3;
    }
    
    .custom-alert button:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.5);
    }

</style>
</head>
<body>
<div id="customAlert" class="custom-alert">
    <h2 id="alertTitle"></h2>
    <button onclick="hideAlert()">Close</button>
</div>

<script>
    function showAlert(title, message) {
        document.getElementById("alertTitle").innerHTML = title;
        document.querySelector('.custom-alert').classList.add('show');
        setTimeout(hideAlert, 2000)

    }
    function hideAlert() {
        document.querySelector('.custom-alert').classList.remove('show');

    }
</script>
</body>
</html>
