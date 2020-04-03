function loadWidget() {
    sessionStorage.removeItem('waifu-text');

    document.body.insertAdjacentHTML('beforeend',`
        <div id="waifu">
            <div id="waifu-tips"></div>
            <canvas id="live2d" width="300" height="300"></canvas>
            <div id="waifu-tool">
                <span class="fa fa-lg fa-comment"></span>
                <span class="fa fa-lg fa-paper-plane"></span>
                <span class="fa fa-lg fa-street-view"></span>
                <span class="fa fa-lg fa-camera-retro"></span>
                <span class="fa fa-lg fa-info-circle"></span>
                <span class="fa fa-lg fa-times"></span>
            </div>
        </div>`);
    setTimeout(() => {
        document.getElementById('waifu').style.bottom = '-10px';
    }, 0);

    function randomSelection(obj) {
        return Array.isArray(obj) ? obj[Math.floor(Math.random() * obj.length)] : obj;
    }

    let userAction = false,
        userActionTimer,
        messageTimer,
        messageArray = ['好久不见，日子过得好快呢……', '大坏蛋！你都多久没理人家了呀，嘤嘤嘤～', '嗨～快来逗我玩吧！', '拿小拳拳锤你胸口！', '记得把小家加入 Adblock 白名单哦！'];
    window.addEventListener('mousemove', () => userAction = true);
    window.addEventListener('keydown', () => userAction = true);
    setInterval(() => {
        if (userAction) {
            userAction = false;
            clearInterval(userActionTimer);
            userActionTimer = null;
        } else if (!userActionTimer) {
            userActionTimer = setInterval(() => {
                showMessage(randomSelection(messageArray), 6000, 9);
            }, 20000);
        }
    }, 1000);

    (function registerEventListener() {
        document.querySelector('#waifu-tool .fa-comment').addEventListener('click', showHitokoto);

        document.querySelector('#waifu-tool .fa-paper-plane').addEventListener('click', () => {
            if (window.Asteroids) {
                if (!window.ASTEROIDSPLAYERS) window.ASTEROIDSPLAYERS = [];
                window.ASTEROIDSPLAYERS.push(new Asteroids());
            } else {
                let script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/gh/GalaxyMimi/CDN/asteroids.js';
                document.head.appendChild(script);
            }
        });

        document.querySelector('#waifu-tool .fa-street-view').addEventListener('click', loadModel);

        document.querySelector('#waifu-tool .fa-camera-retro').addEventListener('click', () => {
            showMessage('照好了嘛，是不是很可爱呢？', 6000, 9);
            Live2D.captureName = 'photo.png';
            Live2D.captureFrame = true;
        });

        document.querySelector('#waifu-tool .fa-info-circle').addEventListener('click', () => {
            open('https://github.com/stevenjoezhang/live2d-widget');
        });

        document.querySelector('#waifu-tool .fa-times').addEventListener('click', () => {
            localStorage.setItem('waifu-display', Date.now());
            // 大切な人といつかまた巡り会えますように
            showMessage("愿你有一天能和你最重要的人重逢。", 2000, 11);
            document.getElementById('waifu').style.bottom = '-500px';
            setTimeout(() => {
                document.getElementById('waifu').style.display = 'none';
            }, 3000);
        });
    })();

    (function welcomeMessage() {
        let text,
            now = new Date().getHours();

        if (now > 5 && now <= 7)
            text = '早上好！一日之计在于晨，美好的一天就要开始了。';
        else if (now > 7 && now <= 11)
            text = '上午好！工作顺利嘛，不要久坐，多起来走动走动哦！';
        else if (now > 11 && now <= 13)
            text = '中午了，工作了一个上午，现在是午餐时间！';
        else if (now > 13 && now <= 17)
            text = '午后很容易犯困呢，今天的运动目标完成了吗？';
        else if (now > 17 && now <= 19)
            text = '傍晚了！窗外夕阳的景色很美丽呢，最美不过夕阳红～';
        else if (now > 19 && now <= 21)
            text = '晚上好，今天过得怎么样？';
        else if (now > 21 && now <= 23)
            text = ['已经这么晚了呀，早点休息吧，晚安～', '深夜时要爱护眼睛呀！'];
        else
            text = '你是夜猫子呀？这么晚还不睡觉，明天起的来嘛？';

        showMessage(text, 7000, 8);
    })();

    window.onscroll = () => {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            document.getElementById('waifu').style.lineHeight = '128px';
        } else {
            document.getElementById('waifu').style.lineHeight = '0px';
        }
    };

    function showHitokoto() {
        fetch('https://v1.hitokoto.cn')
            .then(response => response.json())
            .then(result => {
                let text = `这句一言来自 <span>「${result.from}」</span>，是 <span>${result.creator}</span> 在 hitokoto.cn 投稿的。`;
                showMessage(result.hitokoto, 6000, 9);
                setTimeout(() => {
                    showMessage(text, 4000, 9);
                }, 6000);
            });
    }

    function showMessage(text, timeout, priority) {
        if (!text || (sessionStorage.getItem('waifu-text') && sessionStorage.getItem('waifu-text') > priority)) return;
        if (messageTimer) {
            clearTimeout(messageTimer);
            messageTimer = null;
        }
        text = randomSelection(text);
        sessionStorage.setItem('waifu-text', priority);
        let tips = document.getElementById('waifu-tips');
        tips.innerHTML = text;
        tips.classList.add('waifu-tips-active');
        messageTimer = setTimeout(() => {
            sessionStorage.removeItem('waifu-text');
            tips.classList.remove('waifu-tips-active');
        }, timeout);
    }

    (function initModel() {
        loadModel();

        fetch('https://api.mcstaralliance.com/live2d/message.json')
            .then(response => response.json())
            .then(result => {
                result.mouseover.forEach(tips => {
                    window.addEventListener('mouseover', event => {
                        if (!event.target.matches(tips.selector)) return;
                        let text = randomSelection(tips.text);
                        text = text.replace('{text}', event.target.innerText);
                        showMessage(text, 4000, 8);
                    });
                });

                result.click.forEach(tips => {
                    window.addEventListener('click', event => {
                        if (!event.target.matches(tips.selector)) return;
                        let text = randomSelection(tips.text);
                        text = text.replace('{text}', event.target.innerText);
                        showMessage(text, 4000, 8);
                    });
                });

                result.seasons.forEach(tips => {
                    let now = new Date(),
                        after = tips.date.split('-')[0],
                        before = tips.date.split('-')[1] || after;
                    if ((after.split('/')[0] <= now.getMonth() + 1 && now.getMonth() + 1 <= before.split('/')[0]) && (after.split('/')[1] <= now.getDate() && now.getDate() <= before.split('/')[1])) {
                        var text = Array.isArray(tips.text) ? tips.text[Math.floor(Math.random() * tips.text.length)] : tips.text;
                        text = text.replace('{year}', now.getFullYear());
                        //showMessage(text, 7000, true);
                        messageArray.push(text);
                    }
                });
            });
    })();

    async function loadModel() {
        loadlive2d('live2d', 'https://api.mcstaralliance.com/live2d/Pio/rand.php', console.log('live2d', '模型加载完成'));
    }
}

function loadExternalResource(url, type) {
     return new Promise((resolve, reject) => {
          let elem;

          if (type === 'css') {
               elem = document.createElement('link');
               elem.rel = 'stylesheet';
               elem.href = url;
          } else if (type === 'js') {
               elem = document.createElement('script');
               elem.src = url;
          }

          if (elem) {
               elem.onload = () => resolve(url);
               elem.onerror = () => reject(url);
               document.head.appendChild(elem);
          }
     });
}

if (screen.width >= 768) {
    Promise.all([
        loadExternalResource('https://skin.mcstaralliance.com/plugins/mcstaralliance/assets/css/waifu.css?v=0.1.7', 'css'),
        loadExternalResource('https://skin.mcstaralliance.com/plugins/mcstaralliance/assets/js/live2d.min.js?v=0.1.7', 'js'),
    ]).then(() => {
        if (localStorage.getItem('waifu-display') && Date.now() - localStorage.getItem('waifu-display') <= 86400000) {
            // 已关闭
        } else {
            loadWidget();
        }
    });
}