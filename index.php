<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AI Product Price Finder</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #111827;
        }

        .container {
            width: min(900px, 94%);
            margin: 30px auto;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 30px;
        }

        .header p {
            color: #6b7280;
            margin-top: 8px;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            resize: vertical;
            font-size: 16px;
            outline: none;
        }

        textarea:focus {
            border-color: #f97316;
        }

        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            margin-top: 8px;
            background: #f8fafc;
        }

        button {
            width: 100%;
            border: none;
            background: #f97316;
            color: white;
            padding: 15px;
            border-radius: 12px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 18px;
        }

        button:hover {
            background: #ea580c;
        }

        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        #loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #f97316;
            font-weight: bold;
        }

        #error {
            margin-top: 15px;
        }

        #result {
            margin-top: 15px;
        }

        .answer {
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            line-height: 1.8;
            white-space: normal;
            border: 1px solid #e5e7eb;
        }

        .server-response {
            background: #fff7ed;
            color: #7c2d12;
            padding: 15px;
            border-radius: 12px;
            overflow-x: auto;
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-top: 30px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>🔎 AI Product Price Finder</h1>
        <p>Product সম্পর্কে প্রশ্ন করো অথবা ছবি upload করো</p>
    </div>

    <div class="card">

        <form id="searchForm">

            <label for="question">
                Product সম্পর্কে প্রশ্ন
            </label>

            <textarea
                id="question"
                name="question"
                placeholder="যেমন: JBL Tune 510BT এর বাংলাদেশে বর্তমান দাম কত?"
            ></textarea>


            <label for="image" style="margin-top:18px;">
                Product Image
            </label>

            <input
                type="file"
                id="image"
                name="image"
                accept="image/jpeg,image/png,image/webp"
            >

            <button type="submit" id="searchButton">
                🔍 Search Product
            </button>

        </form>

    </div>


    <div id="loading">
        ⏳ Gemini AI গবেষণা করছে...
    </div>


    <div id="error"></div>


    <div id="result"></div>


    <div class="footer">
        Powered by Gemini AI
    </div>

</div>


<script>

const form = document.getElementById("searchForm");
const resultBox = document.getElementById("result");
const loading = document.getElementById("loading");
const errorBox = document.getElementById("error");
const searchButton = document.getElementById("searchButton");


form.addEventListener("submit", async function(e) {

    e.preventDefault();

    resultBox.innerHTML = "";
    errorBox.innerHTML = "";

    loading.style.display = "block";
    searchButton.disabled = true;
    searchButton.textContent = "⏳ Searching...";


    const formData = new FormData(form);


    try {

        const response = await fetch("api.php", {

            method: "POST",

            body: formData

        });


        const rawText = await response.text();


        console.log("SERVER RESPONSE:", rawText);


        loading.style.display = "none";


        if (!rawText.trim()) {

            throw new Error(
                "Server returned an empty response."
            );

        }


        let data;


        try {

            data = JSON.parse(rawText);

        } catch (jsonError) {

            resultBox.innerHTML = `
                <div class="server-response">

                    <strong>⚠️ Server Response:</strong>

                    <pre>${escapeHtml(rawText)}</pre>

                </div>
            `;

            return;

        }


        if (data.error) {

            errorBox.innerHTML = `

                <div class="server-response">

                    ❌ <strong>Error:</strong>

                    <br><br>

                    ${escapeHtml(data.error)}

                </div>

            `;

            return;

        }


        if (data.answer) {

            resultBox.innerHTML = `

                <div class="answer">

                    <h3>🤖 AI Answer</h3>

                    <div>
                        ${formatAnswer(data.answer)}
                    </div>

                </div>

            `;

        } else {

            resultBox.innerHTML = `

                <div class="server-response">

                    ⚠️ Gemini কোনো Answer পাঠায়নি।

                    <br><br>

                    <pre>${escapeHtml(
                        JSON.stringify(data, null, 2)
                    )}</pre>

                </div>

            `;

        }

    }

    catch(error) {

        loading.style.display = "none";

        errorBox.innerHTML = `

            <div class="server-response">

                ❌ <strong>Request Failed</strong>

                <br><br>

                ${escapeHtml(error.message)}

            </div>

        `;

    }

    finally {

        searchButton.disabled = false;

        searchButton.textContent = "🔍 Search Product";

    }

});


function escapeHtml(text) {

    return String(text)

        .replace(/&/g, "&amp;")

        .replace(/</g, "&lt;")

        .replace(/>/g, "&gt;")

        .replace(/"/g, "&quot;")

        .replace(/'/g, "&#039;");

}


function formatAnswer(text) {

    return escapeHtml(text)

        .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")

        .replace(/\n/g, "<br>");

}

</script>

</body>
</html>
