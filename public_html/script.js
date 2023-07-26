function populateClients() {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        var clients = JSON.parse(xhr.responseText);
        var clientsDropdown = document.getElementById("clients");

        // Clear the existing options
        clientsDropdown.innerHTML = "";

        // Add new options based on the retrieved data
        clients.forEach(function (client) {
          var option = document.createElement("option");
          option.value = client.clientID;
          option.text = client.fname + " " + client.lname;
          clientsDropdown.appendChild(option);
        });
      } else {
        console.error("Request failed with status:", xhr.status);
      }
    }
  };
  xhr.open("GET", "getClients.php", true);
  xhr.send();
}

// Call the function to populate the drop-down menu when the page loads
window.addEventListener("DOMContentLoaded", populateClients);

async function calculateHash() {
  const imageFile = document.getElementById('imageFile').files[0];
  if (!imageFile) {
    alert('Please select an image file.');
    return;
  }

  const reader = new FileReader();
  reader.onload = async function (e) {
    const imageBuffer = e.target.result;
    const hashBuffer = await crypto.subtle.digest('SHA-256', imageBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');
    console.log('Hash value (hex):', hashHex);

    const form = document.getElementById('myForm');
    const hashInput = document.createElement('input');
    hashInput.type = 'hidden';
    hashInput.name = 'hashHex';
    hashInput.value = hashHex;
    form.appendChild(hashInput);

  };
  reader.readAsArrayBuffer(imageFile);
}
