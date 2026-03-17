const canvas = document.getElementById("flappyCanvas");
const ctx = canvas.getContext("2d");

let running = false;
let gameOver = false;

let bird = {
  x: 60,
  y: 200,
  width: 25,
  height: 25,
  velocity: 0,
  gravity: 0.25,
  jump: -6,
};

let pipes = [];
let pipeGap = 140;
let pipeWidth = 50;
let frame = 0;

document.addEventListener("keydown", (e) => {
  if (e.code === "Space") {
    if (!running && !gameOver) {
      startGame();
    } else if (running) {
      bird.velocity = bird.jump;
    } else if (gameOver) {
      resetGame();
    }
  }
});

function startGame() {
  running = true;
  gameLoop();
}

function resetGame() {
  bird.y = 200;
  bird.velocity = 0;
  pipes = [];
  frame = 0;
  gameOver = false;
  running = false;
  drawStartScreen();
}

function addPipe() {
  let topHeight = Math.random() * 200 + 50;
  pipes.push({
    x: canvas.width,
    top: topHeight,
    bottom: topHeight + pipeGap,
  });
}

function gameLoop() {
  if (!running) return;

  frame++;

  bird.velocity += bird.gravity;
  bird.y += bird.velocity;

  if (frame % 90 === 0) addPipe();

  pipes.forEach((pipe) => (pipe.x -= 2));

  pipes = pipes.filter((pipe) => pipe.x > -pipeWidth);

  for (let pipe of pipes) {
    if (
      bird.x < pipe.x + pipeWidth &&
      bird.x + bird.width > pipe.x &&
      (bird.y < pipe.top || bird.y + bird.height > pipe.bottom)
    ) {
      endGame();
    }
  }

  if (bird.y > canvas.height - bird.height || bird.y < 0) {
    endGame();
  }

  draw();
  if (running) requestAnimationFrame(gameLoop);
}

function endGame() {
  running = false;
  gameOver = true;
  drawGameOver();
}

function draw() {
  ctx.fillStyle = "#87CEEB";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "yellow";
  ctx.fillRect(bird.x, bird.y, bird.width, bird.height);

  ctx.fillStyle = "green";
  pipes.forEach((pipe) => {
    ctx.fillRect(pipe.x, 0, pipeWidth, pipe.top);
    ctx.fillRect(pipe.x, pipe.bottom, pipeWidth, canvas.height - pipe.bottom);
  });
}

function drawStartScreen() {
  draw();
  ctx.fillStyle = "rgba(0,0,0,0.5)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "white";
  ctx.font = "22px Arial";
  ctx.fillText("Nyomd meg a SPACE-t", 70, 240);
  ctx.fillText("a kezdéshez", 120, 270);
}

function drawGameOver() {
  draw();
  ctx.fillStyle = "rgba(0,0,0,0.6)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "white";
  ctx.font = "24px Arial";
  ctx.fillText("Vége!", 150, 220);
  ctx.font = "18px Arial";
  ctx.fillText("SPACE = új játék", 110, 260);
}

drawStartScreen();
