// makes "processing" text visible or hides it
// @Params:{visible = true or false whether hide or show text}
function processing(visible){
    var loading_box = document.getElementById("processing");
    if(visible){
        loading_box.removeAttribute("hidden");
        if(document.querySelector("#upload_text") !== null && document.querySelector("#upload_text") !== undefined ){
            document.querySelector("#upload_text").setAttribute("hidden", "true");

        }

    }else{
        loading_box.setAttribute("hidden", "true");
    }
}

// used to add value to checkboxes
function check_box(){
    boxes = document.querySelectorAll("[type=checkbox]");
    boxes.forEach(function(box){
        box.addEventListener("click",function(){
            if( box.value === ""){
                box.value = true;
            }else{
                box.value = "";
            }
        })
    })

}

// async function that fetches response from "find-density.php";
// bound to "Calculate Density" button;
async function density(){
    processing(true);
    // the div where the results and messages will be displayed
    let results_div = document.querySelector("#results");
    let tables_id = ['keywords','one-word','two-words','three-words', 'four-words'];
    // create a FormData and append the url and checkboxes values;
    let form_data = new FormData();
    url_selector = document.querySelector("#url");
    boxes = document.querySelectorAll("[type=checkbox]");
    form_data.append('url', url_selector.value);
    boxes.forEach(function(box,i){
        form_data.append(box.name, box.value);
    })

    // cleares the results div
    function clear_results(){
        results_div.innerHTML = "";
    }

    // creates tables containing the results and adds headers for each table
    // @params { json = json recieved from fetch, tables = array with tables ids};
    function create_tables(json,tables){
        let table_heads = ['Keyword','Frequency','Density','Title','Description','H-tags'];
        tables.forEach((id,index) => {
            if( json[id].length === 0){
                if(id === "keywords"){
                    var heading = document.createElement('h5');
                    heading.classList.add('text-center','mt-3',"text-danger");
                    heading.innerHTML = "The page does not have keywords!";
                    results_div.appendChild(heading);
                }
                return;
            }
            result_table = document.createElement('table');
            result_table.style.fontSize = "15px";
            var heading = document.createElement('h5');
            heading.innerHTML = 'Results for ' + id;
            heading.classList.add('text-center','mt-3');
            result_table.classList.add('table', 'table-success', 'table-striped' , 'm-auto')
            result_table.setAttribute('id', id);
            table_head = document.createElement('thead');
            table_heads.forEach( thead => {
                head = document.createElement('th');
                head.innerHTML = thead;
                head.classList.add('text-center');
                table_head.appendChild(head);
            })
            result_table.appendChild(table_head);
            table_body = document.createElement('tbody');
            result_table.appendChild(table_body);
            // the keywords table will be the first to be desplayed in a different manner
            if( id === "keywords" ){
                result_table.classList.add("w-75")
                results_div.appendChild(heading);
                results_div.appendChild(result_table);

            }else{
                result_table.classList.add("w-100")
                if( (index + 1) % 2 == 0){
                    let row = document.createElement("div");
                    row.classList.add('row','table-row');
                    let col = document.createElement("div");
                    col.classList.add('col');
                    row.appendChild(col);
                    col.appendChild(heading);
                    col.appendChild(result_table);
                    results_div.appendChild(row);
                }
                if( index % 2 == 0 && index !== 0){
                    let col = document.createElement("div");
                    col.classList.add('col');
                    col.appendChild(heading);
                    col.appendChild(result_table);
                    let rows = document.getElementsByClassName('table-row');
                    rows[rows.length-1].appendChild(col)
                }
            }
        });
    }
    
    // populates created tables with results from json
    // @params { result = array of results from first JSON entry; res_table = HTML table element }
    function populate_table(result, res_table){
        for (const [word,vals] of Object.entries(result)) {
            if( result.length === 0){
                return;
            }
            var row = res_table.insertRow();
            var cell = row.insertCell(0);
            cell.classList.add("text-center","align-items-middle","p-absolute");
            cell.innerHTML = word;
            Object.values(vals).forEach(function(val,index){
                var cell = row.insertCell(index + 1);
                cell.classList.add("text-center","align-middle");
                cell.innerHTML = val;
            })

          }
    }

    // creates a HTML heading element containing an error message recieved from fethc if there is any;
    // @param { status = string containing message };
    function create_status_message(status = ""){
        if( status !== ""){
            let text = document.createElement("h2");
            text.innerHTML = status;
            results_div.appendChild(text);
        }
    }

    // function called when the fetch has succeeded
    // @param { json = JSON };
    function success(json){
        clear_results();
        if( json["status"] !== null && json["status"] !== undefined){
            create_status_message(json["status"]);
        }else{
            create_tables(json,tables_id);
            tables_id.forEach( id => {
                populate_table(json[id], document.querySelector("#" + id));
            })
        }
        processing(false);
        results_div.removeAttribute("hidden");
    }
    function failure(e){
        console.log(e);
    }

    let response = await fetch("find-density.php",{
        method: "POST",
        body: form_data,
        success: function(data){
        }
    }).then(res => res.json())
    .then(response => success(response))
    .catch(error => failure(error));
}