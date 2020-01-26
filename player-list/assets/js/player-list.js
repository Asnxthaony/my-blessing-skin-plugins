fetch('https://s1-api.mcstaralliance.com/onlinePlayers')
    .then(response => response.json())
    .then(data => {
        document.querySelector('#player-list').innerHTML = '';

        for (var i in data) {
            var name = data[i];
            var color = 'black';

            if (name.indexOf('[管理员]') != -1) {
                name = name.replace('[管理员]', '');
                color = 'red';
            }

            if (name.indexOf('[开发者]') != -1) {
                name = name.replace('[开发者]', '');
                color = 'orange';
            }

            displayName = name;
            if  (name.length >= 12) {
                displayName = name.slice(0, 9) + '...'
            }

            document.querySelector('#player-list').innerHTML += `
                <div class="col-12 col-sm-12 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1">
                            <img src="avatar/player/` + name + `" alt="` + name + `" width="45" height="45">
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-` + color + `" title="` + name + `">` + displayName + `</span>
                            <span class="info-box-number">
                                ❤
                            </span>
                        </div>
                    </div>
                </div>
            `
        }
    })
    .catch(err => {})