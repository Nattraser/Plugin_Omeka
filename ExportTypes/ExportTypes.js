/**
* Permet l'ajout du bouton d'export dans le menu des utilisateurs, ainsi que la 
* création de la modale de choix d'export
*/
window.onload = (event) => {
    let button = document.createElement("a");
    button.id = "export-button";
	button.class = "button";
	button.text = "Exporter";
	button.href = "#";
	button.style = "width : 90px; height: 30px; position :relative; bottom:0px;left : calc(50% - 45px); text-align:center; color:white; background : black;";
    document.getElementById("search-container").appendChild(button);
	
	let showExport = document.createElement("div");
	showExport.id = "show-export";
	let QRCode = document.createElement("a");
	QRCode.href = "#";
	QRCode.text = "QR Code";
	let PDF = document.createElement("a");
	PDF.href = "#";
	PDF.text = "PDF";
	let Link = document.createElement("a");
	Link.href = "#";
	Link.text = "Lien";
	
	showExport.appendChild(QRCode);
	showExport.appendChild(PDF);
	showExport.appendChild(Link);
	document.getElementById("search-container").appendChild(showExport);
	
	button.addEventListener("click",popUpShow);
	QRCode.addEventListener("click",()=>{exportShow("QRCodePopUp")});
	PDF.addEventListener("click",()=>{exportShow("PDFPopUp")});
	Link.addEventListener("click",()=>{exportShow("LinkPopUp")});	
};


/**
* Permet l'affichage de la modal de chois d'export
*/
function popUpShow(){
	let buttonStyle = document.getElementById("show-export").style.display;
	if (buttonStyle !== "flex"){document.getElementById("show-export").style.display = "flex";}
	else {document.getElementById("show-export").style.display = "none";}
};


/**
* Permet l'affichage de la modal de l'export choisi, ainsi que la génération des éléments
* dépendents
* @param idPopUp : identifiant html de la modale choisie
*/ 
function exportShow(idPopUp){
	document.getElementById("show-export").style.display = "none";
	
	if(idPopUp === "QRCodePopUp"){
		const qrCode = new QRCodeStyling({
        	width: 200,
        	height: 200,
        	type: "canvas",
        	data: window.location.href,
        	dotsOptions: {
            	color: "black",
            	type: "rounded"
        	},
        	backgroundOptions: {
            	color: "white",
        	},
        	imageOptions: {
            	crossOrigin: "anonymous",
            	margin: 40
        	}
    	});
	
		qrCode.append(document.getElementById("QRCodeImg"));
	}
	
	if(idPopUp === "LinkPopUp") document.getElementById("link").innerHTML = window.location.href;
	document.getElementById(idPopUp).style.display = "flex";
};


/**
* Permet la fermeture de la modale passé en paramètre
* @Param popUp : identifiant html de la modale à cacher
*/
function popUpClose(popUp){
	document.getElementById(popUp).style.display="none";
	//document.getElementById("popUpDiv").style.display = "none";
};


/**
* Permet le téléchargement automatique du QR Code
*/
function downloadQRCode(){

  var canvas = document.getElementsByTagName('canvas')[0];
  var image = canvas.toDataURL("image/png");
  

  var link = document.createElement('a');
  link.download = "QRCode.png";
  link.href = image;

  document.body.appendChild(link);
  link.click();
  
  document.body.removeChild(link);
};


/**
* Permet l'affichage de la fenêtre d'impression
*/
function printPDF(){
	document.getElementById("PDFPopUp").style.display="none";
	window.print();
};


/** 
* Permet le téléchargement du PDF 
*/
function downloadPDF(){
	var element = document.getElementById('content');

	var doc = new jsPDF();
  	var specialElementHandlers = {
    	'#editor': function (element, renderer) {
      	return true;
    	}
  	};
  	doc.fromHTML(element, 15,15, {
    'width': 170,
    'elementHandlers': specialElementHandlers
  	});
  	doc.save('document.pdf');

};

/**
* Permet de copier dans le presse papier le lien d'export
*/
function copy(){
  	event.preventDefault();
	let link = document.getElementById("link").textContent;
  	navigator.clipboard.writeText(link);
};