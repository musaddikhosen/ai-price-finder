<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AI Product Price Finder</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family:
                Arial,
                "Noto Sans Bengali",
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #fff7ed,
                    #ffffff
                );

            color: #111827;

            min-height: 100vh;

        }


        .container {

            width: min(
                900px,
                92%
            );

            margin:
                40px auto;

        }


        .header {

            text-align: center;

            margin-bottom: 25px;

        }


        .header h1 {

            font-size: 34px;

            margin-bottom: 10px;

        }


        .header p {

            color: #6b7280;

            line-height: 1.6;

        }


        .card {

            background: white;

            padding: 25px;

            border-radius: 20px;

            box-shadow:
                0 10px 40px
                rgba(
                    0,
                    0,
                    0,
                    .08
                );

            margin-bottom: 20px;

        }


        textarea {

            width: 100%;

            min-height: 130px;

            padding: 16px;

            border:
                1px solid #d1d5db;

            border-radius: 14px;

            font-size: 16px;

            resize: vertical;

            outline: none;

        }


        textarea:focus {

            border-color:
                #f97316;

        }


        .upload {

            margin-top: 15px;

            padding: 20px;

            border:
                2px dashed #d1d5db;

            border-radius: 14px;

            text-align: center;

        }


        input[type="file"] {

            margin-top: 12px;

            width: 100%;

        }


        button {

            width: 100%;

            border: none;

            border-radius: 14px;

            padding: 16px;

            margin-top: 18px;

            background:
                #f97316;

            color: white;

            font-size: 17px;

            font-weight: bold;

            cursor: pointer;

        }


        button:hover {

            background:
                #ea580c;

        }


        button:disabled {

            background:
                #9ca3af;

            cursor:
                not-allowed;

        }


        .loading {

            display: none;

            text-align: center;

        }


        .result {

            display: none;

        }


        .result-text {

            margin-top: 15px;

            white-space: pre-wrap;

            line-height: 1.8;

        }


        .error {

            display: none;

            margin-top: 15px;

            padding: 15px;

            border-radius: 12px;

            background: #fee2e2;

            color: #991b1b;

        }


        @media(max-width:600px) {

            .container {

                margin-top: 20px;

            }


            .header h1 {

                font-size: 27px;

            }


            .card {

                padding: 18px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="header">

        <h1>
            🤖 AI Product Price Finder
        </h1>

        <p>
            Product-এর নাম অথবা ছবি দিয়ে
            AI-এর সাহায্যে তথ্য ও সম্ভাব্য
            বাংলাদেশি বাজারদাম জানুন।
        </p>

    </div>



    <div class="card">


        <textarea
            id="question"
            placeholder="যেমন: JBL Tune 510BT এর বাংলাদেশে বর্তমান দাম কত?"
        ></textarea>



        <div class="upload">

            <strong>
                অথবা Product-এর ছবি দিন
            </strong>

            <br>

            <input
                type="file"
                id="image"
                accept="image/*"
            >

        </div>



        <button
            id="searchButton"
        >
            🔍 Product Search
        </button>



        <div
            id="error"
            class="error"
        ></div>


    </div>



    <div
        id="loading"
        class="card loading"
    >

        <h3>
            🤖 AI কাজ করছে...
        </h3>

        <p>
            একটু অপেক্ষা করুন।
        </p>

    </div>



    <div
        id="result"
        class="card result"
    >

        <h2>
            📊 AI Result
        </h2>


        <div
            id="resultText"
            class="result-text"
        ></div>

    </div>


</div>



<script>


const button =
    document.getElementById(
        "searchButton"
    );


const question =
    document.getElementById(
        "question"
    );


const image =
    document.getElementById(
        "image"
    );


const loading =
    document.getElementById(
        "loading"
    );


const result =
    document.getElementById(
        "result"
    );


const resultText =
    document.getElementById(
        "resultText"
    );


const errorBox =
    document.getElementById(
        "error"
    );



button.addEventListener(
    "click",
    async function () {


        const text =
            question.value.trim();


        const imageFile =
            image.files[0];



        if (
            !text &&
            !imageFile
        ) {

            showError(
                "Product-এর নাম অথবা ছবি দিন।"
            );

            return;

        }



        hideError();


        result.style.display =
            "none";


        loading.style.display =
            "block";


        button.disabled =
            true;



        try {


            const formData =
                new FormData();


            formData.append(
                "question",
                text
            );



            if (imageFile) {

                formData.append(
                    "image",
                    imageFile
                );

            }



            const response =
                await fetch(
                    "api.php",
                    {

                        method: "POST",

                        body: formData

                    }
                );



            const data =
                await response.json();



            if (!response.ok) {

                throw new Error(
                    data.message ||
                    "Server error"
                );

            }



            resultText.textContent =
                data.answer;


            result.style.display =
                "block";


        }

        catch(error) {


            showError(
                error.message
            );


        }

        finally {


            loading.style.display =
                "none";


            button.disabled =
                false;

        }

    }
);



function showError(message) {

    errorBox.textContent =
        message;

    errorBox.style.display =
        "block";

}



function hideError() {

    errorBox.style.display =
        "none";

}


</script>


</body>

</html>
