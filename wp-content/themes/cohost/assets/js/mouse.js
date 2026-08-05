const bob = document.getElementsByClassName('custom-cursor')[0];

let mouseX = 0;
let mouseY = 0;

let ballX = 0;
let ballY = 0;

let speed = 0.2;  // How fast ball catches up to mouse pointer

function animate() {
  let distX = mouseX - ballX;
  let distY = mouseY - ballY;

  ballX = ballX + (distX * speed);
  ballY = ballY + (distY * speed);

  bob.style.left = ballX + 'px';
  bob.style.top = ballY + 'px';

  requestAnimationFrame(animate);
}

animate();

document.addEventListener('mousemove', function (e) {
  mouseX = e.clientX;
  mouseY = e.clientY;
});

document.addEventListener('click', function (e) {
  e.preventDefault();
  bob.classList.remove('active');
  void bob.offsetWidth; // Re-trigger animation
  bob.classList.add('active');
});
