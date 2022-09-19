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

async function density(){
    processing(true);
    let tables_id = ['keywords','one-word','two-words','three-words', 'four-words'];
    let form_data = new FormData();
    url_selector = document.querySelector("#url");
    boxes = document.querySelectorAll("[type=checkbox]");
    form_data.append('url', url_selector.value);
    boxes.forEach(function(box,i){
        form_data.append(box.name, box.value);
    })

    function remove_tables(tables){
        let results_div = document.querySelector("#results");
        tables.forEach(id => {
            to_remove = document.getElementById(id);
            if( to_remove !== null && to_remove !== undefined){
                to_remove.remove();
            }
        })

    }

    function create_tables(tables){
        let results_div = document.querySelector("#results");
        let table_heads = ['Keyword','Frequency','Density','Title','Description','H-tags'];
        tables.forEach((id,index) => {

            result_table = document.createElement('table');
            result_table.style.fontSize = "16px";
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
    
    function populate_table(arr, res_table){
        // console.log(res_table)
        for (const [word,vals] of Object.entries(arr)) {
            // console.log(Object.values(vals));

            var row = res_table.insertRow();
            var cell = row.insertCell(0);
            cell.classList.add("text-center");
            cell.innerHTML = word;
            Object.values(vals).forEach(function(val,index){
                var cell = row.insertCell(index + 1);
                cell.classList.add("text-center");
                cell.innerHTML = val;
                // console.log(val)
            })

          }
    }

    function success(json){
        // console.log( typeof full_array[0   ])
        remove_tables(tables_id);
        create_tables(tables_id);
        tables_id.forEach( id => {
            populate_table(json[id], document.querySelector("#" + id));
        })
        processing(false);
        document.querySelector("#results").removeAttribute("hidden");
        

    }

    function failure(e){
        console.log(e);
    }

    let response = await fetch("find-density",{
        method: "POST",
        body: form_data,
        success: function(data){
        }
    }).then(res => res.json())
    .then(response => success(response))
    .catch(error => failure(error));
}