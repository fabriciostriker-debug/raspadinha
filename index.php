<?php
// Redirecionar para a página inicial correta
header('Location: inicio.php');
exit();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fortuna PIX - Jogo de Raspadinha com Notas de Dinheiro</title>
    <style>
/* Reset básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, #8B5CF6, #A855F7);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
    user-select: none; /* Evita seleção de texto durante raspagem */
}

.game-container {
    background: rgba(139, 92, 246, 0.9);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 100%;
    text-align: center;
}

.header {
    margin-bottom: 20px;
}

.banner {
    width: 100%;
    max-width: 280px;
    height: auto;
    margin-bottom: 15px;
}

.balance-button {
    background: #22C55E;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.main-scratch-container {
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    width: 100%;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
    aspect-ratio: 1;
    background: black;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.main-scratch-container::before {
    content: "🎉 PRÊMIOS AQUI! 🎉";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: bold;
    font-size: 18px;
    z-index: 1;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.main-scratch-image {
    width: 100%;
    max-width: 300px;
    height: auto;
    aspect-ratio: 1;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    display: none; /* Escondido pois usaremos canvas */
}

/* Estilos para canvas de raspagem */
.scratch-canvas {
    border-radius: 15px;
    touch-action: none; /* Evita scroll no mobile durante raspagem */
}

.individual-scratch-canvas {
    border-radius: 10px;
    touch-action: none;
}

.scratch-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 20px;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
    transition: all 0.5s ease;
}

.scratch-grid.hidden {
    display: none;
}

.scratch-grid.visible {
    display: grid;
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.scratch-area {
    aspect-ratio: 1;
    background: white;
    border: 2px solid #22C55E;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    font-weight: bold;
    font-size: 16px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 80px;
}

/* Efeito de brilho para áreas não raspadas */
.scratch-area:not(.prize):not(.money-note):not(.nothing) {
    background: linear-gradient(45deg, #F3F4F6, #E5E7EB, #F3F4F6);
    background-size: 200% 200%;
    animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.scratch-area.prize {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    border-color: #F59E0B;
    color: #92400E;
    animation: prizeGlow 1s ease-in-out;
}

@keyframes prizeGlow {
    0% { box-shadow: 0 0 5px rgba(245, 158, 11, 0.5); }
    50% { box-shadow: 0 0 20px rgba(245, 158, 11, 0.8); }
    100% { box-shadow: 0 0 5px rgba(245, 158, 11, 0.5); }
}

.scratch-area.money-note {
    background: linear-gradient(135deg, #DBEAFE, #BFDBFE);
    border-color: #3B82F6;
    color: #1E40AF;
    padding: 10px; /* Aumentado o padding para diminuir a imagem */
    animation: moneyPop 0.5s ease-out;
}

.scratch-area.money-note img {
    width: 110%; /* Diminuído o tamanho da imagem */
    height: ; /* Diminuído o tamanho da imagem */
    object-fit: contain; /* Garante que a imagem inteira seja visível */
    border-radius: 0px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

@keyframes moneyPop {
    0% { transform: scale(0.5); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.scratch-area.nothing {
    background: linear-gradient(135deg, #FEE2E2, #FECACA);
    border-color: #EF4444;
    color: #991B1B;
    animation: fadeInSlow 0.5s ease-in;
}

@keyframes fadeInSlow {
    from { opacity: 0; }
    to { opacity: 1; }
}

.footer {
    text-align: center;
}

.message {
    color: white;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 15px;
    min-height: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.play-again-button {
    background: #22C55E;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.play-again-button:hover {
    background: #16A34A;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.play-again-button:active {
    transform: translateY(0);
}

.play-again-button.visible {
    display: inline-block;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Indicador de progresso visual */
.scratch-hint {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.main-scratch-container:hover .scratch-hint {
    opacity: 1;
}

/* Responsividade para mobile */
@media (max-width: 480px) {
    body {
        padding: 5px;
    }
    
    .game-container {
        padding: 15px;
        max-width: 350px;
    }
    
    .banner {
        max-width: 250px;
    }
    
    .balance-button {
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .main-scratch-container {
        max-width: 280px;
    }
    
    .main-scratch-container::before {
        font-size: 16px;
    }
    
    .scratch-grid {
        max-width: 280px;
        gap: 6px;
    }
    
    .scratch-area {
        font-size: 14px;
        min-height: 70px;
    }
    
    .message {
        font-size: 14px;
    }
    
    .play-again-button {
        font-size: 14px;
        padding: 10px 20px;
    }
}

/* Responsividade para tablets */
@media (min-width: 481px) and (max-width: 768px) {
    .game-container {
        max-width: 450px;
        padding: 25px;
    }
    
    .main-scratch-container {
        max-width: 350px;
    }
    
    .main-scratch-container::before {
        font-size: 20px;
    }
    
    .scratch-grid {
        max-width: 350px;
        gap: 10px;
    }
    
    .scratch-area {
        font-size: 18px;
        min-height: 90px;
    }
}

/* Responsividade para desktop */
@media (min-width: 769px) {
    .game-container {
        max-width: 500px;
        padding: 30px;
    }
    
    .banner {
        max-width: 320px;
    }
    
    .balance-button {
        font-size: 16px;
        padding: 10px 20px;
    }
    
    .main-scratch-container {
        max-width: 380px;
    }
    
    .main-scratch-container::before {
        font-size: 22px;
    }
    
    .scratch-grid {
        max-width: 380px;
        gap: 12px;
    }
    
    .scratch-area {
        font-size: 20px;
        min-height: 100px;
    }
    
    .message {
        font-size: 18px;
    }
    
    .play-again-button {
        font-size: 18px;
        padding: 14px 28px;
    }
}

/* Estilos para melhor experiência de raspagem */
.scratch-canvas:hover {
    cursor: crosshair;
}

.individual-scratch-canvas:hover {
    cursor: crosshair;
}

/* Evita seleção de texto e outros comportamentos indesejados */
.game-container * {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    -webkit-touch-callout: none;
    -webkit-tap-highlight-color: transparent;
}
    </style>
    <script>
// Lógica do jogo Fortuna PIX com raspagem gradual realística
// Versão apenas notas: todas as 9 posições sempre exibem notas de dinheiro
class FortunaPixGame {
    constructor() {
        this.mainScratchContainer = document.querySelector(".main-scratch-container");
        this.scratchGrid = document.querySelector(".scratch-grid");
        this.scratchAreas = document.querySelectorAll(".scratch-area");
        this.messageElement = document.querySelector(".message");
        this.playAgainButton = document.querySelector(".play-again-button");
        
        this.gameResults = [];
        this.hasWinner = false;
        this.winningPrize = null;
        this.winningNote = null;
        this.isMainScratched = false;
        
        // Configurações da raspagem
        this.scratchRadius = 25;
        this.scratchThreshold = 0.8; // 80% da área deve ser raspada
        
        // Mapeamento de notas de dinheiro
        this.moneyNotes = {
            "2": "./assets/money_notes/2REAIS.jpg",
            "3": "./assets/money_notes/3REAIS.jpg", 
            "5": "./assets/money_notes/5REAIS.png",
            "10": "./assets/money_notes/10REAIS.png",
            "50": "./assets/money_notes/50REAIS.png"
        };
        
        this.init();
    }

    init() {
        this.setupMainScratchArea();
        this.setupPlayAgainButton();
        this.generateGameResults();
    }

    setupMainScratchArea() {
        // Cria canvas para a raspagem principal
        this.createMainScratchCanvas();
    }

    createMainScratchCanvas() {
        const container = this.mainScratchContainer;
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        
        canvas.className = "scratch-canvas";
        canvas.style.position = "absolute";
        canvas.style.top = "0";
        canvas.style.left = "0";
        canvas.style.cursor = "crosshair";
        canvas.style.zIndex = "10";
        
        // Ajusta o tamanho do canvas
        const rect = container.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        canvas.style.width = rect.width + "px";
        canvas.style.height = rect.height + "px";
        
        // Desenha a camada de raspagem
        ctx.fillStyle = "#8B5CF6";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Adiciona padrão de raspagem
        this.drawScratchPattern(ctx, canvas.width, canvas.height);
        
        // Adiciona texto "RASPE AQUI"
        ctx.fillStyle = "white";
        ctx.font = "bold 24px Arial";
        ctx.textAlign = "center";
        ctx.fillText("RASPE AQUI", canvas.width / 2, canvas.height / 2);
        
        container.appendChild(canvas);
        
        this.mainCanvas = canvas;
        this.mainCtx = ctx;
        this.setupMainScratchEvents();
    }

    drawScratchPattern(ctx, width, height) {
        // Cria padrão diagonal para simular área de raspagem
        ctx.strokeStyle = "rgba(255, 255, 255, 0.1)";
        ctx.lineWidth = 2;
        
        for (let i = 0; i < width + height; i += 10) {
            ctx.beginPath();
            ctx.moveTo(i, 0);
            ctx.lineTo(i - height, height);
            ctx.stroke();
        }
    }

    setupMainScratchEvents() {
        let isScratching = false;
        let scratchedPixels = 0;
        const totalPixels = this.mainCanvas.width * this.mainCanvas.height;
        
        // Eventos para mouse
        this.mainCanvas.addEventListener("mousedown", (e) => {
            isScratching = true;
            this.scratch(e, "mouse");
        });
        
        this.mainCanvas.addEventListener("mousemove", (e) => {
            if (isScratching) {
                scratchedPixels += this.scratch(e, "mouse");
                this.checkMainScratchProgress(scratchedPixels, totalPixels);
            }
        });
        
        this.mainCanvas.addEventListener("mouseup", () => {
            isScratching = false;
        });
        
        // Eventos para touch (mobile)
        this.mainCanvas.addEventListener("touchstart", (e) => {
            e.preventDefault();
            isScratching = true;
            this.scratch(e.touches[0], "touch");
        });
        
        this.mainCanvas.addEventListener("touchmove", (e) => {
            e.preventDefault();
            if (isScratching) {
                scratchedPixels += this.scratch(e.touches[0], "touch");
                this.checkMainScratchProgress(scratchedPixels, totalPixels);
            }
        });
        
        this.mainCanvas.addEventListener("touchend", (e) => {
            e.preventDefault();
            isScratching = false;
        });
    }

    scratch(event, type) {
        const rect = this.mainCanvas.getBoundingClientRect();
        let x, y;
        
        if (type === "mouse") {
            x = event.clientX - rect.left;
            y = event.clientY - rect.top;
        } else {
            x = event.clientX - rect.left;
            y = event.clientY - rect.top;
        }
        
        // Ajusta coordenadas para o canvas real
        x = x * (this.mainCanvas.width / rect.width);
        y = y * (this.mainCanvas.height / rect.height);
        
        // Remove a área raspada
        this.mainCtx.globalCompositeOperation = "destination-out";
        this.mainCtx.beginPath();
        this.mainCtx.arc(x, y, this.scratchRadius, 0, 2 * Math.PI);
        this.mainCtx.fill();
        
        // Retorna área aproximada raspada
        return Math.PI * this.scratchRadius * this.scratchRadius;
    }

    checkMainScratchProgress(scratchedPixels, totalPixels) {
        const progress = scratchedPixels / totalPixels;
        
        if (progress >= this.scratchThreshold && !this.isMainScratched) {
            this.isMainScratched = true;
            this.revealMainArea();
        }
    }

    revealMainArea() {
        // Remove o canvas de raspagem
        this.mainCanvas.style.opacity = "0";
        
        setTimeout(() => {
            this.mainScratchContainer.style.display = "none";
            this.scratchGrid.classList.remove("hidden");
            this.scratchGrid.classList.add("visible");
            
            // Revela todos os prêmios automaticamente
            this.revealAllPrizes();
        }, 500);
    }

    revealAllPrizes() {
        this.scratchAreas.forEach((area, index) => {
            const result = this.gameResults[index];
            
            // Animação escalonada para cada área
            setTimeout(() => {
                // Todas as posições agora sempre exibem notas de dinheiro
                if (Object.keys(this.moneyNotes).includes(result)) {
                    // Cria imagem da nota
                    const img = document.createElement("img");
                    img.src = this.moneyNotes[result];
                    img.alt = `Nota de R$ ${result}`;
                    
                    area.innerHTML = "";
                    area.appendChild(img);
                    area.classList.add("money-note");
                }
                
                // Animação de revelação
                area.style.transform = "scale(1.1)";
                setTimeout(() => {
                    area.style.transform = "scale(1)";
                }, 200);
                
            }, index * 150); // Delay escalonado de 150ms entre cada área
        });
        
        // Mostra o resultado final após todas as áreas serem reveladas
        setTimeout(() => {
            this.showFinalResult();
        }, 9 * 150 + 500); // Aguarda todas as animações + 500ms extra
    }

    setupPlayAgainButton() {
        this.playAgainButton.addEventListener("click", () => this.resetGame());
    }

    generateGameResults() {
        const possibleNotes = ["2", "3", "5", "10", "50"]; // Notas de dinheiro disponíveis
        
        this.gameResults = [];
        this.hasWinner = false;
        this.winningPrize = null;
        this.winningNote = null;

        // Decide se haverá um prêmio (chance de 30% de ter 3 iguais)
        const shouldWin = Math.random() < 0.3;

        if (shouldWin) {
            // Escolhe a nota vencedora
            const winningNote = possibleNotes[Math.floor(Math.random() * possibleNotes.length)];
            this.winningNote = winningNote;
            this.hasWinner = true;
            
            // Calcula o prêmio dinamicamente: valor da nota × 3
            const noteValue = parseInt(winningNote);
            this.winningPrize = `R$ ${(noteValue * 3).toFixed(2).replace('.', ',')}`;

            // Cria array com exatamente 3 notas vencedoras em posições aleatórias
            const winningPositions = [];
            while (winningPositions.length < 3) {
                const randomPos = Math.floor(Math.random() * 9);
                if (!winningPositions.includes(randomPos)) {
                    winningPositions.push(randomPos);
                }
            }

            // Preenche as 9 posições
            for (let i = 0; i < 9; i++) {
                if (winningPositions.includes(i)) {
                    // Posição vencedora: coloca a nota vencedora
                    this.gameResults.push(winningNote);
                } else {
                    // Posição não vencedora: coloca placeholder temporário
                    this.gameResults.push("placeholder");
                }
            }

            // Preenche as posições não vencedoras apenas com notas de dinheiro
            this.fillNonWinningPositionsOnlyNotes(winningNote, winningPositions);

        } else {
            // Sem vitória: preenche com notas aleatórias, garantindo que não haja 3 iguais
            for (let i = 0; i < 9; i++) {
                let note = possibleNotes[Math.floor(Math.random() * possibleNotes.length)];
                this.gameResults.push(note);
            }
            // Verifica e corrige se acidentalmente gerou 3 iguais
            this.ensureNoThreeIdenticalOnlyNotes();
        }
    }

    fillNonWinningPositionsOnlyNotes(winningNote, winningPositions) {
        const possibleNotes = ["2", "3", "5", "10", "50"];
        
        // Remove a nota vencedora das opções para evitar conflitos
        const availableNotes = possibleNotes.filter(note => note !== winningNote);
        
        // Contador para controlar quantas vezes cada nota aparece
        const noteCounts = {};
        availableNotes.forEach(note => noteCounts[note] = 0);
        
        // Preenche as posições não vencedoras
        for (let i = 0; i < 9; i++) {
            if (!winningPositions.includes(i)) {
                // Filtra notas que ainda podem ser usadas (aparecem menos de 2 vezes)
                const availableNotesForPosition = availableNotes.filter(note => noteCounts[note] < 2);
                
                if (availableNotesForPosition.length > 0) {
                    const selectedNote = availableNotesForPosition[Math.floor(Math.random() * availableNotesForPosition.length)];
                    this.gameResults[i] = selectedNote;
                    noteCounts[selectedNote]++;
                } else {
                    // Se todas as notas já apareceram 2 vezes, escolhe uma aleatória
                    // (isso é raro, mas garante que sempre seja uma nota)
                    const randomNote = availableNotes[Math.floor(Math.random() * availableNotes.length)];
                    this.gameResults[i] = randomNote;
                }
            }
        }
    }

    ensureNoThreeIdenticalOnlyNotes() {
        let needsReshuffle = true;
        const possibleNotes = ["2", "3", "5", "10", "50"];
        
        while (needsReshuffle) {
            needsReshuffle = false;
            const counts = {};
            for (const note of this.gameResults) {
                counts[note] = (counts[note] || 0) + 1;
                if (counts[note] >= 3) {
                    needsReshuffle = true;
                    // Se encontrou 3 iguais, troca um deles para uma nota diferente
                    const indexToChange = this.gameResults.lastIndexOf(note);
                    
                    let newNote = possibleNotes[Math.floor(Math.random() * possibleNotes.length)];
                    while (newNote === note) {
                        newNote = possibleNotes[Math.floor(Math.random() * possibleNotes.length)];
                    }
                    this.gameResults[indexToChange] = newNote;
                    break; // Sai do loop interno e verifica novamente
                }
            }
        }
    }

    checkWinCondition() {
        const counts = {};
        for (const note of this.gameResults) {
            counts[note] = (counts[note] || 0) + 1;
            if (counts[note] >= 3) {
                this.hasWinner = true;
                this.winningNote = note;
                
                // Calcula o prêmio dinamicamente baseado no valor da nota
                if (Object.keys(this.moneyNotes).includes(note)) {
                    const noteValue = parseInt(note);
                    this.winningPrize = `R$ ${(noteValue * 3).toFixed(2).replace('.', ',')}`;
                } else {
                    // Caso seja algum outro tipo de resultado (não deveria acontecer)
                    this.winningPrize = "R$ 0,00";
                }
                return;
            }
        }
        this.hasWinner = false;
    }

    showFinalResult() {
        this.checkWinCondition(); // Verifica a condição de vitória antes de mostrar o resultado

        if (this.hasWinner) {
            this.messageElement.textContent = `🎉 Parabéns! Você ganhou ${this.winningPrize}! 🎉`;
            this.messageElement.style.color = "#22C55E";
        } else {
            this.messageElement.textContent = "Não foi dessa vez. 😔";
            this.messageElement.style.color = "#EF4444";
        }
        
        this.playAgainButton.classList.add("visible");
    }

    resetGame() {
        // Reset das variáveis
        this.hasWinner = false;
        this.winningPrize = null;
        this.winningNote = null;
        this.isMainScratched = false;
        
        // Remove canvas existentes
        const existingCanvases = document.querySelectorAll(".scratch-canvas, .individual-scratch-canvas");
        existingCanvases.forEach(canvas => canvas.remove());
        
        // Reset da interface
        this.mainScratchContainer.style.display = "flex";
        this.scratchGrid.classList.add("hidden");
        this.scratchGrid.classList.remove("visible");
        
        // Reset das áreas individuais
        this.scratchAreas.forEach(area => {
            area.classList.remove("prize", "money-note", "nothing");
            area.innerHTML = "RASPE AQUI";
            area.style.transform = "scale(1)";
            area.style.position = "static";
        });
        
        // Reset da mensagem e botão
        this.messageElement.textContent = "";
        this.messageElement.style.color = "white";
        this.playAgainButton.classList.remove("visible");
        
        // Gera novos resultados e recria a área principal
        this.generateGameResults();
        this.createMainScratchCanvas();
    }
}

// Inicializa o jogo quando a página carrega
document.addEventListener("DOMContentLoaded", () => {
    new FortunaPixGame();
});
    </script>
</head>
<body>
    <div class="game-container">
        <div class="header">
            <img src="./assets/banner_fortuna_pix.png" alt="Fortuna PIX Banner" class="banner">
            <div class="balance-button">Saldo: R$ <?= number_format($saldo, 2, ',', '.') ?></div>
        </div>
        
        <!-- Container principal para raspagem -->
        <div class="main-scratch-container">
            <div class="scratch-hint">Arraste o mouse ou dedo para raspar até 80%!</div>
            <!-- Canvas será criado dinamicamente aqui -->
        </div>
        
        <!-- Grid de áreas individuais -->
        <div class="scratch-grid hidden">
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
            <div class="scratch-area">RASPE AQUI</div>
        </div>
        
        <div class="footer">
            <p class="message"></p>
            <button class="play-again-button">🎮 Jogar Novamente</button>
        </div>
    </div>
</body>
</html>
