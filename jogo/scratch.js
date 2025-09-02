// Lógica do jogo Fortuna PIX com raspagem autêntica
class FortunaPixGame {
    constructor() {
        // Elementos da interface
        this.scratchContainer = document.querySelector('.scratch-container');
        this.scratchGrid = document.querySelector('.scratch-grid');
        this.scratchAreas = document.querySelectorAll('.scratch-area');
        this.messageElement = document.querySelector('.message');
        this.playAgainButton = document.querySelector('.play-again-button');
        // Removida referência ao balanceButton que não existe na interface
        
        // Estado do jogo
        this.gameResults = [];
        this.hasWinner = false;
        this.winningPrize = null;
        this.winningNote = null;
        this.gameStarted = false;
        this.gameBlocked = false;
        this.revealedEnough = false;
        
        // Obter valor da aposta da URL
        const params = new URLSearchParams(window.location.search);
        this.valorAposta = parseFloat(params.get('valor')) || 1;
        
        // Configurações de raspagem
        this.scratchRadius = 20;
        this.areaRevelationThreshold = 0.7; // Percentual para considerar jogo revelado suficiente (30%)
        
        // Mapeamento de notas de dinheiro
        this.moneyNotes = {
            '2': './assets/money_notes/2REAIS.jpg',
            '3': './assets/money_notes/3REAIS.jpg', 
            '5': './assets/money_notes/5REAIS.png',
            '10': './assets/money_notes/10REAIS.png',
            '50': './assets/money_notes/50REAIS.png'
        };
        
        this.init();
    }

    async init() {
        // Verificar se tem saldo e inicializar o jogo
        await this.fetchBalance();
        await this.generateGameResults();
        this.setupGame();
        this.setupPlayAgainButton();
        
        // Inicializar áudio
        this.initAudio();
    }
    
    initAudio() {
        // Criar elementos de áudio para os sons
        this.scratchSound = new Audio('../assets/audio/raspar.mp3');
        this.scratchSound.volume = 0.2;
        this.winSound = new Audio('../assets/audio/ganhou.mp3');
        this.winSound.volume = 0.5;
        this.loseSound = new Audio('../assets/audio/perdeu.mp3');
        this.loseSound.volume = 0.5;
    }

    setupGame() {
        // Primeiro, preencher as áreas com os resultados (que ficarão embaixo da camada raspável)
        this.setupResultAreas();
        
        // Criar uma camada única de raspagem sobre toda a grade
        this.createScratchLayer();
    }
    
    setupResultAreas() {
        // Preencher cada área com seu resultado (que ficará oculto pela camada de raspagem)
        this.scratchAreas.forEach((area, index) => {
            const result = this.gameResults[index];
            
            // Limpar conteúdo anterior
            while (area.firstChild) {
                area.removeChild(area.firstChild);
            }
            
            // Se for uma nota de dinheiro, adicionar imagem
            if (Object.keys(this.moneyNotes).includes(result)) {
                const img = document.createElement('img');
                img.src = this.moneyNotes[result];
                img.alt = 'Nota de R$ ' + result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'contain';
                area.appendChild(img);
            } else {
                // Caso excepcional (não deve ocorrer nesta versão)
                area.textContent = result;
            }
        });
    }

    createScratchLayer() {
        // Dimensões da grade completa
        const gridRect = this.scratchGrid.getBoundingClientRect();
        
        // Adicionar um evento de duplo clique para revelar o resultado (para testes)
        this.scratchGrid.addEventListener('dblclick', () => {
            if (this.gameStarted && !this.revealedEnough) {
                this.forceReveal();
            }
        });
        
        // Criar canvas que cobre toda a grade
        const canvas = document.createElement('canvas');
        canvas.className = 'scratch-canvas';
        canvas.width = gridRect.width;
        canvas.height = gridRect.height;
        canvas.style.position = 'absolute';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.cursor = 'crosshair';
        canvas.style.borderRadius = '10px';
        canvas.style.zIndex = '100';
        
        // Configurar o contexto do canvas
        const ctx = canvas.getContext('2d');
        
        // Criar uma área intermediária que receberá a imagem de raspagem
        ctx.fillStyle = '#7C3AED'; // Cor roxa de fallback
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Carregar a imagem de raspagem
        const scratchImage = new Image();
        scratchImage.onload = () => {
            // Quando a imagem carregar, desenhá-la no canvas
            ctx.drawImage(scratchImage, 0, 0, canvas.width, canvas.height);
            
            // Adicionar texto sobre a imagem
            ctx.fillStyle = 'white';
            ctx.font = 'bold 24px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('RASPE AQUI', canvas.width / 2, canvas.height / 2);
            
            // Adicionar padrão de raspagem
            this.drawScratchPattern(ctx, canvas.width, canvas.height);
        };
        
        // Definir o src da imagem após configurar o handler onload
        scratchImage.src = './assets/123.png';
        
        // Em caso de erro de carregamento da imagem
        scratchImage.onerror = () => {
            console.error('Erro ao carregar a imagem de raspagem');
            // Continuamos com o fallback roxo e adicionamos texto
            ctx.fillStyle = 'white';
            ctx.font = 'bold 24px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('RASPE AQUI', canvas.width / 2, canvas.height / 2);
            
            // Adicionar padrão de raspagem
            this.drawScratchPattern(ctx, canvas.width, canvas.height);
        };
        
        // Adicionar o canvas ao container
        this.scratchContainer.appendChild(canvas);
        
        // Guardar referências
        this.canvas = canvas;
        this.ctx = ctx;
        
        // Configurar eventos de raspagem
        this.setupScratchEvents();
    }

    drawScratchPattern(ctx, width, height) {
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 2;
        
        // Padrão de linhas diagonais
        for (let i = 0; i < width + height; i += 20) {
            ctx.beginPath();
            ctx.moveTo(i, 0);
            ctx.lineTo(i - height, height);
            ctx.stroke();
        }
    }

    setupScratchEvents() {
        let isScratching = false;
        let scratchedPixels = 0;
        const totalPixels = this.canvas.width * this.canvas.height;
        
        // Criar mapa de bits para rastrear pixels raspados (evitar contagem duplicada)
        const pixelSize = 8; // Aumentado de 4 para 8 para melhorar performance
        const gridWidth = Math.ceil(this.canvas.width / pixelSize);
        const gridHeight = Math.ceil(this.canvas.height / pixelSize);
        this.pixelMap = new Array(gridWidth * gridHeight).fill(false);
        this.totalPixelMapSize = this.pixelMap.length;
        
        // Variáveis para throttling
        let lastCheckTime = 0;
        const checkThrottle = 150; // Verificar porcentagem raspada a cada 150ms
        
        // Mapa para rastrear quais áreas já foram suficientemente raspadas
        this.scratchedMap = new Array(this.scratchAreas.length).fill(0);
        
        // Eventos para mouse
        this.canvas.addEventListener('mousedown', async (e) => {
            e.preventDefault();
            if (this.gameBlocked) return;
            
            // Se for a primeira raspagem, iniciar o jogo
            if (!this.gameStarted) {
                const canStart = await this.startGame();
                if (!canStart) return;
            }
            
            isScratching = true;
            this.scratch(e, 'mouse');
            
            // Iniciar som de raspagem
            this.playScratchSound();
        });
        
        this.canvas.addEventListener('mousemove', (e) => {
            if (isScratching && !this.gameBlocked) {
                scratchedPixels += this.scratch(e, 'mouse');
                
                // Throttle para reduzir a frequência de verificações
                const now = Date.now();
                if (now - lastCheckTime > checkThrottle && !this.revealedEnough) {
                    lastCheckTime = now;
                    
                    // Estimar porcentagem raspada sem verificar todos os pixels a cada vez
                    const scratchedCount = Math.min(
                        scratchedPixels, 
                        this.totalPixelMapSize * 0.8  // Limite para evitar overflow
                    );
                    const percentScratched = scratchedCount / this.totalPixelMapSize;
                    
                    // Remover logs para melhorar performance
                    
                    if (percentScratched >= this.areaRevelationThreshold && !this.revealedEnough) {
                        this.revealedEnough = true;
                        this.checkWinCondition();
                    }
                }
            }
        });
        
        this.canvas.addEventListener('mouseup', () => {
            isScratching = false;
            this.stopScratchSound();
        });
        
        this.canvas.addEventListener('mouseout', () => {
            isScratching = false;
            this.stopScratchSound();
        });
        
        // Eventos para dispositivos móveis (touch)
        this.canvas.addEventListener('touchstart', async (e) => {
            e.preventDefault();
            if (this.gameBlocked) return;
            
            // Se for a primeira raspagem, iniciar o jogo
            if (!this.gameStarted) {
                const canStart = await this.startGame();
                if (!canStart) return;
            }
            
            isScratching = true;
            this.scratch(e.touches[0], 'touch');
            
            // Iniciar som de raspagem
            this.playScratchSound();
        });
        
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (isScratching && !this.gameBlocked) {
                scratchedPixels += this.scratch(e.touches[0], 'touch');
                
                // Usar o mesmo throttling do mouse
                const now = Date.now();
                if (now - lastCheckTime > checkThrottle && !this.revealedEnough) {
                    lastCheckTime = now;
                    
                    // Estimar porcentagem raspada para evitar calcular a cada movimento
                    const scratchedCount = Math.min(
                        scratchedPixels, 
                        this.totalPixelMapSize * 0.8  // Limite para evitar overflow
                    );
                    const percentScratched = scratchedCount / this.totalPixelMapSize;
                    
                    // Remover logs para melhorar performance
                    
                    if (percentScratched >= this.areaRevelationThreshold && !this.revealedEnough) {
                        this.revealedEnough = true;
                        this.checkWinCondition();
                    }
                }
            }
        });
        
        this.canvas.addEventListener('touchend', (e) => {
            e.preventDefault();
            isScratching = false;
            this.stopScratchSound();
        });
        
        this.canvas.addEventListener('touchcancel', (e) => {
            e.preventDefault();
            isScratching = false;
            this.stopScratchSound();
        });
    }
    
    playScratchSound() {
        // Reproduzir som de raspagem
        if (this.scratchSound.paused) {
            this.scratchSound.currentTime = 0;
            this.scratchSound.play().catch(e => console.log('Erro ao reproduzir áudio de raspagem:', e));
        }
    }
    
    stopScratchSound() {
        // Parar som de raspagem
        if (!this.scratchSound.paused) {
            this.scratchSound.pause();
            this.scratchSound.currentTime = 0;
        }
    }

    async startGame() {
        // Iniciar o jogo e descontar o saldo
        this.gameStarted = true;
        return await this.deductBalance();
    }

    async deductBalance() {
        // Descontar o valor da aposta do saldo
        const headers = { 'Content-Type': 'application/json' };
        const body = { valor_aposta: this.valorAposta };
        
        try {
            const response = await fetch('descontar_saldo.php', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(body)
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.erro || 'Erro desconhecido');
            }
            
            const data = await response.json();
            
            if (data.sucesso) {
                if (data.saldo) {
                    this.updateBalance(data.saldo);
                    
                    // Atualizar também o saldo na função global
                    if (typeof window.updateBalance === 'function') {
                        window.updateBalance();
                    }
                }
                return true; // Saldo descontado com sucesso
            } else {
                if (data.erro) {
                    console.error('Erro do servidor:', data.erro);
                    this.showErrorMessage('Erro: ' + data.erro);
                    this.blockGame();
                }
                return false;
            }
        } catch (error) {
            console.error('Erro ao descontar saldo:', error);
            const errorMsg = error.message === 'Saldo insuficiente' ? 
                'Saldo insuficiente ❌' : 
                'Erro ao processar pagamento ❌';
                
            this.showErrorMessage(errorMsg);
            this.blockGame();
            return false;
        }
    }

    scratch(event, type) {
        if (this.gameBlocked) return 0;
        
        const rect = this.canvas.getBoundingClientRect();
        let x, y;
        
        if (type === 'mouse') {
            x = event.clientX - rect.left;
            y = event.clientY - rect.top;
        } else {
            x = event.clientX - rect.left;
            y = event.clientY - rect.top;
        }
        
        // Ajustar coordenadas para o tamanho real do canvas
        const canvasX = x * (this.canvas.width / rect.width);
        const canvasY = y * (this.canvas.height / rect.height);
        
        // Raspar área circular no canvas, criando um efeito de transparência
        this.ctx.globalCompositeOperation = 'destination-out';
        this.ctx.beginPath();
        this.ctx.arc(canvasX, canvasY, this.scratchRadius, 0, 2 * Math.PI);
        this.ctx.fill();
        
        // Usar o mesmo tamanho de pixel da configuração para consistência e melhor performance
        const pixelSize = 8; // Deve ser o mesmo valor definido em setupScratchEvents
        let newlyScratchedPixels = 0;
        
        // Calcular limites da área de raspagem para reduzir os cálculos
        const radius = this.scratchRadius;
        const gridWidth = Math.ceil(this.canvas.width / pixelSize);
        
        // Simplificar: usar um quadrado em vez de verificar a distância de cada pixel
        const minPx = Math.max(0, Math.floor((canvasX - radius) / pixelSize));
        const maxPx = Math.min(Math.ceil(this.canvas.width / pixelSize) - 1, Math.floor((canvasX + radius) / pixelSize));
        const minPy = Math.max(0, Math.floor((canvasY - radius) / pixelSize));
        const maxPy = Math.min(Math.ceil(this.canvas.height / pixelSize) - 1, Math.floor((canvasY + radius) / pixelSize));
        
        // Verificar apenas pixels dentro do quadrado (mais rápido que calcular distância para cada um)
        for (let py = minPy; py <= maxPy; py++) {
            for (let px = minPx; px <= maxPx; px++) {
                const index = py * gridWidth + px;
                
                // Se o pixel ainda não foi raspado, marcá-lo como raspado
                if (!this.pixelMap[index]) {
                    this.pixelMap[index] = true;
                    newlyScratchedPixels++;
                }
            }
        }
        
        // Retornar número de novos pixels raspados (sem duplicação)
        return newlyScratchedPixels;
    }

    checkWinCondition() {
        const counts = {};
        
        // Contar ocorrências de cada tipo de resultado
        for (const value of this.gameResults) {
            counts[value] = (counts[value] || 0) + 1;
            
            // Se encontrou 3 iguais, temos um vencedor
            if (counts[value] >= 3) {
                this.hasWinner = true;
                this.winningNote = value;
                
                // Calcular o prêmio baseado no valor da nota
                if (Object.keys(this.moneyNotes).includes(value)) {
                    const noteValue = parseInt(value);
                    this.winningPrizeNumeric = noteValue; // Prêmio é o valor integral da nota
                    this.winningPrize = 'R$ ' + noteValue.toFixed(2).replace('.', ',');
                }
                
                // Mostrar resultado apenas quando área suficiente foi raspada
                this.showFinalResult();
                return true;
            }
        }
        
        // Se não tiver vencedor, também mostra o resultado final
        if (this.revealedEnough) {
            this.showFinalResult();
        }
        
        return false;
    }
    
    // Método para revelar o resultado forçadamente (útil para debugging)
    forceReveal() {
        if (!this.gameStarted || this.revealedEnough) return;
        
        // Marcar como revelado
        this.revealedEnough = true;
        
        // Verificar resultado
        this.checkWinCondition();
        
        // Tornar o canvas mais transparente para mostrar o resultado
        if (this.canvas) {
            this.canvas.style.opacity = '0.1';
        }
    }

    async generateGameResults() {
        const possibleValues = ['2', '3', '5', '10', '50']; // Notas disponíveis
        this.gameResults = [];
        this.hasWinner = false;
        this.winningPrize = null;
        this.winningNote = null;
        
        try {
            // Consultar API para determinar se deve ganhar
            const response = await fetch('../admin/api.php?t=' + Date.now());
            const data = await response.json();
            
            const shouldWin = data.win === true;
            
            if (shouldWin) {
                // Escolher nota vencedora aleatória
                const winningValue = possibleValues[Math.floor(Math.random() * possibleValues.length)];
                this.winningNote = winningValue;
                this.hasWinner = true;
                
                // Calcular prêmio
                const prizeValue = parseInt(winningValue);
                this.winningPrizeNumeric = prizeValue;
                this.winningPrize = 'R$ ' + prizeValue.toFixed(2).replace('.', ',');
                
                // Escolher 3 posições para as notas vencedoras
                const winningPositions = [];
                while (winningPositions.length < 3) {
                    const pos = Math.floor(Math.random() * 9);
                    if (!winningPositions.includes(pos)) winningPositions.push(pos);
                }
                
                // Preencher as 9 posições
                for (let i = 0; i < 9; i++) {
                    if (winningPositions.includes(i)) {
                        this.gameResults.push(winningValue);
                    } else {
                        this.gameResults.push('placeholder');
                    }
                }
                
                // Preencher posições não vencedoras com outras notas
                this.fillNonWinningPositions(winningValue, winningPositions);
                
            } else {
                // Sem vitória: preencher com notas aleatórias
                for (let i = 0; i < 9; i++) {
                    const value = possibleValues[Math.floor(Math.random() * possibleValues.length)];
                    this.gameResults.push(value);
                }
                
                // Garantir que não haja 3 notas iguais (para evitar vitória acidental)
                this.ensureNoThreeIdentical();
            }
            
        } catch (error) {
            console.error('Erro ao consultar vitória:', error);
            
            // Fallback: gerar resultados localmente
            const shouldWin = Math.random() < 0.3; // 30% de chance de vitória
            
            if (shouldWin) {
                const winningValue = possibleValues[Math.floor(Math.random() * possibleValues.length)];
                this.winningNote = winningValue;
                this.hasWinner = true;
                
                const prizeValue = parseInt(winningValue);
                this.winningPrizeNumeric = prizeValue;
                this.winningPrize = 'R$ ' + prizeValue.toFixed(2).replace('.', ',');
                
                const winningPositions = [];
                while (winningPositions.length < 3) {
                    const pos = Math.floor(Math.random() * 9);
                    if (!winningPositions.includes(pos)) winningPositions.push(pos);
                }
                
                for (let i = 0; i < 9; i++) {
                    if (winningPositions.includes(i)) {
                        this.gameResults.push(winningValue);
                    } else {
                        this.gameResults.push('placeholder');
                    }
                }
                
                this.fillNonWinningPositions(winningValue, winningPositions);
                
            } else {
                for (let i = 0; i < 9; i++) {
                    const value = possibleValues[Math.floor(Math.random() * possibleValues.length)];
                    this.gameResults.push(value);
                }
                
                this.ensureNoThreeIdentical();
            }
        }
    }

    fillNonWinningPositions(winningValue, winningPositions) {
        const allValues = ['2', '3', '5', '10', '50'];
        const otherValues = allValues.filter(val => val !== winningValue);
        const usageCount = {};
        otherValues.forEach(val => usageCount[val] = 0);
        
        for (let i = 0; i < 9; i++) {
            if (!winningPositions.includes(i)) {
                const availableValues = otherValues.filter(val => usageCount[val] < 2);
                
                if (availableValues.length > 0) {
                    const selectedValue = availableValues[Math.floor(Math.random() * availableValues.length)];
                    this.gameResults[i] = selectedValue;
                    usageCount[selectedValue]++;
                } else {
                    const fallbackValue = otherValues[Math.floor(Math.random() * otherValues.length)];
                    this.gameResults[i] = fallbackValue;
                }
            }
        }
    }

    ensureNoThreeIdentical() {
        let hasThreeIdentical = true;
        const allValues = ['2', '3', '5', '10', '50'];
        
        while (hasThreeIdentical) {
            hasThreeIdentical = false;
            const counts = {};
            
            for (const value of this.gameResults) {
                counts[value] = (counts[value] || 0) + 1;
                
                if (counts[value] >= 3) {
                    hasThreeIdentical = true;
                    const lastIndex = this.gameResults.lastIndexOf(value);
                    let newValue = allValues[Math.floor(Math.random() * allValues.length)];
                    
                    while (newValue === value) {
                        newValue = allValues[Math.floor(Math.random() * allValues.length)];
                    }
                    
                    this.gameResults[lastIndex] = newValue;
                    break;
                }
            }
        }
    }

    showFinalResult() {
        console.log("Mostrando resultado final:", this.hasWinner ? "GANHOU" : "PERDEU");
        
        // Limpar a mensagem de texto abaixo do jogo para evitar duplicação
        this.messageElement.textContent = '';
        
        // Destacar as notas vencedoras se houver vitória
        if (this.hasWinner && this.winningNote) {
            // Adicionar destaque visual aos cards com as notas vencedoras
            this.scratchAreas.forEach((area, index) => {
                if (this.gameResults[index] === this.winningNote) {
                    area.classList.add('winning-card');
                }
            });
        }
        
        // Mostrar mensagem visual diretamente no scratch-grid
        this.showResultMessage(this.hasWinner, this.winningPrize);
        
        // Reproduzir sons adequados
        if (this.hasWinner && this.winningPrize) {
            // Reproduzir som de vitória
            this.winSound.play().catch(e => console.log('Erro ao reproduzir áudio de vitória:', e));
        } else {
            // Reproduzir som de derrota
            this.loseSound.play().catch(e => console.log('Erro ao reproduzir áudio de derrota:', e));
        }
        
        // Mostrar botão para jogar novamente
        this.playAgainButton.classList.add('visible');
        
        // Enviar resultado para o servidor
        let prizeValue = 0;
        if (this.winningPrizeNumeric) {
            prizeValue = this.winningPrizeNumeric;
        } else if (this.winningPrize && typeof this.winningPrize === 'string') {
            prizeValue = parseFloat(this.winningPrize.replace('R$ ', '').replace(',', '.')) || 0;
        }
        
        this.sendGameResult(this.hasWinner, prizeValue);
    }
    
    // Função para mostrar mensagem de resultado como elemento HTML no scratch-grid
    showResultMessage(isWinner, prize) {
        // Remover qualquer mensagem existente
        const existingMessage = document.querySelector('.result-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Manter o canvas visível mas com transparência para ver o resultado por baixo
        if (this.canvas) {
            this.canvas.style.opacity = '0.3';
            this.canvas.style.pointerEvents = 'none'; // Desabilitar interação
        }
        
        // Criar elemento de mensagem de resultado
        const messageDiv = document.createElement('div');
        messageDiv.className = 'result-message';
        
        // Estilizar como uma faixa na parte superior da raspadinha
        messageDiv.style.position = 'absolute';
        messageDiv.style.top = '20px'; // Um pouco abaixo do topo
        messageDiv.style.left = '50%';
        messageDiv.style.transform = 'translateX(-50%)';
        messageDiv.style.width = '80%'; // Não ocupa toda a largura
        messageDiv.style.minHeight = '90px'; // Altura fixa menor
        messageDiv.style.display = 'flex';
        messageDiv.style.flexDirection = 'column';
        messageDiv.style.justifyContent = 'center';
        messageDiv.style.alignItems = 'center';
        messageDiv.style.borderRadius = '12px';
        messageDiv.style.padding = '10px';
        messageDiv.style.boxSizing = 'border-box';
        messageDiv.style.zIndex = '200';
        
        // Definir cores com base no resultado com alguma transparência
        if (isWinner) {
            messageDiv.style.background = 'linear-gradient(135deg, rgba(0, 170, 70, 0.85) 0%, rgba(0, 128, 0, 0.85) 100%)';
            messageDiv.style.border = '2px solid #FFD700';
            messageDiv.style.boxShadow = '0 0 15px rgba(255, 215, 0, 0.6)';
        } else {
            messageDiv.style.background = 'linear-gradient(135deg, rgba(170, 0, 0, 0.85) 0%, rgba(128, 0, 0, 0.85) 100%)';
            messageDiv.style.border = '2px solid #8B0000';
            messageDiv.style.boxShadow = '0 0 15px rgba(139, 0, 0, 0.6)';
        }
        
        // Criar conteúdo interno
        const emoji = document.createElement('div');
        emoji.style.fontSize = '36px';
        emoji.style.marginBottom = '10px';
        emoji.textContent = isWinner ? '🎉' : '😢';
        
        const title = document.createElement('div');
        title.style.fontSize = '24px';
        title.style.fontWeight = 'bold';
        title.style.color = 'white';
        title.style.marginBottom = '10px';
        title.style.textShadow = '0 2px 4px rgba(0, 0, 0, 0.5)';
        title.textContent = isWinner ? 'PARABÉNS!' : 'NÃO FOI DESSA VEZ!';
        
        const subtitle = document.createElement('div');
        subtitle.style.fontSize = '18px';
        subtitle.style.color = 'white';
        subtitle.style.textShadow = '0 1px 2px rgba(0, 0, 0, 0.5)';
        subtitle.textContent = isWinner ? `Você ganhou ${prize}!` : 'Tente novamente!';
        
        // Adicionar elementos à mensagem
        messageDiv.appendChild(emoji);
        messageDiv.appendChild(title);
        messageDiv.appendChild(subtitle);
        
        // Adicionar mensagem ao container
        this.scratchContainer.appendChild(messageDiv);
        
        // Adicionar animação de pulsação
        messageDiv.style.animation = 'pulse 1.5s infinite ease-in-out';
        
        // Configurar timer para remover a mensagem após 3 segundos
        setTimeout(() => {
            // Verificar se a mensagem ainda existe
            if (messageDiv && messageDiv.parentNode) {
                // Adicionar animação de fade-out
                messageDiv.style.animation = 'fadeOut 0.5s forwards';
                
                // Remover após a animação terminar
                setTimeout(() => {
                    if (messageDiv && messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 500);
            }
        }, 3000);
    }

    sendGameResult(won, prize) {
        const headers = { 'Content-Type': 'application/json' };
        const body = { 
            ganhou: won, 
            premio: prize, 
            valor_aposta: this.valorAposta 
        };
        
        fetch('registrar_jogada.php', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(body)
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.sucesso) {
                if (data.saldo) {
                    this.updateBalance(data.saldo);
                    
                    // Atualizar também o saldo na função global para atualizar todos os elementos
                    if (typeof window.updateBalance === 'function') {
                        window.updateBalance();
                    }
                }
            } else {
                if (data.erro) {
                    console.error('Erro do servidor:', data.erro);
                    this.showErrorMessage('Erro: ' + data.erro);
                }
            }
        })
        .catch(error => {
            console.error('Erro de conexão:', error);
            this.showErrorMessage('Erro de conexão. Tente novamente.');
        });
    }

    blockGame() {
        this.gameBlocked = true;
        
        // Desabilitar a raspagem
        if (this.canvas) {
            this.canvas.style.pointerEvents = 'none';
            this.canvas.style.opacity = '0.5';
        }
    }

    async fetchBalance() {
        try {
            const response = await fetch('get_balance.php');
            const data = await response.json();
            
            if (data.saldo) {
                this.updateBalance(data.saldo);
                
                // Verificar se tem saldo suficiente
                const saldoNum = parseFloat(data.saldo.replace('.', '').replace(',', '.'));
                if (saldoNum < this.valorAposta) {
                    this.showErrorMessage('Saldo insuficiente ❌');
                    this.blockGame();
                    return false;
                }
                
                return true;
            }
        } catch (error) {
            console.error('Erro ao buscar saldo:', error);
            return false;
        }
    }

    updateBalance(balance) {
        // Atualizar exibição do saldo
        if (this.balanceButton) {
            this.balanceButton.textContent = 'Saldo: R$ ' + balance;
        }
        
        // Atualizar também outros elementos de saldo, se existirem
        const saldoDesktop = document.getElementById('saldoDesktop');
        if (saldoDesktop) {
            saldoDesktop.textContent = 'R$ ' + balance;
        }
        
        const saldoMobile = document.getElementById('saldoMobile');
        if (saldoMobile) {
            saldoMobile.textContent = 'Saldo: R$ ' + balance;
        }
    }

    showErrorMessage(message) {
        // Mostrar mensagem de erro na interface
        const errorDiv = document.createElement('div');
        errorDiv.textContent = message;
        errorDiv.style.position = 'fixed';
        errorDiv.style.top = '20px';
        errorDiv.style.left = '50%';
        errorDiv.style.transform = 'translateX(-50%)';
        errorDiv.style.backgroundColor = '#EF4444';
        errorDiv.style.color = 'white';
        errorDiv.style.padding = '10px 20px';
        errorDiv.style.borderRadius = '5px';
        errorDiv.style.zIndex = '1000';
        errorDiv.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
        
        document.body.appendChild(errorDiv);
        
        // Se a mensagem for sobre saldo insuficiente, exibir permanentemente
        if (message.includes('Saldo insuficiente')) {
            this.messageElement.textContent = message;
            this.messageElement.style.color = '#EF4444';
        }
        
        // Remover após alguns segundos
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    setupPlayAgainButton() {
        this.playAgainButton.addEventListener('click', () => this.resetGame());
    }

    async resetGame() {
        // Verificar se o jogo está bloqueado
        if (this.gameBlocked) {
            await this.fetchBalance();
            return;
        }
        
        // Reset das variáveis
        this.hasWinner = false;
        this.winningPrize = null;
        this.winningNote = null;
        this.gameStarted = false;
        this.revealedEnough = false;
        
        // Remover o canvas de raspagem
        if (this.canvas) {
            this.canvas.isRemoved = true; // Marca para parar animações
            this.canvas.remove();
        }
        
        // Remover mensagem de resultado se existir
        const resultMessage = document.querySelector('.result-message');
        if (resultMessage) {
            resultMessage.remove();
        }
        
        // Reset das áreas de resultado
        this.scratchAreas.forEach(area => {
            area.innerHTML = '';
            area.className = 'scratch-area'; // Remove todas as classes, incluindo winning-card
        });
        
        // Reset da mensagem e botão
        this.messageElement.textContent = '';
        this.messageElement.style.color = 'white';
        this.playAgainButton.classList.remove('visible');
        
        // Forçar atualização do saldo imediatamente
        if (typeof window.updateBalance === 'function') {
            window.updateBalance();
        }
        
        // Regenerar o jogo
        await this.generateGameResults();
        this.setupGame();
    }
}

// Inicializar o jogo quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    new FortunaPixGame();
});
