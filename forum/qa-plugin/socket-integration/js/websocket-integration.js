document.addEventListener('DOMContentLoaded', function runWebSocketIntegration() {
	const { pathname, hostname, protocol } = window.location;
	const PORT = window.WS_PORT || 3000;
	const isMainOrActivityPage = /^$|\/$|^(\/?)activity/.test(pathname);

	if (!isMainOrActivityPage) {
		return;
	}

	const QA_Q_LIST_SELECTOR = '.qa-q-list';
	const questionList = document.querySelector(QA_Q_LIST_SELECTOR);
	const newContentNotifier = createNewContentNotifier();
	const webSocket = setupWebSocketConnection();

	function setupWebSocketConnection() {
		const socketProtocol = protocol === 'https:' ? 'wss' : 'ws';
		const webSocket = new WebSocket(`${socketProtocol}://${hostname}:${PORT}`);
		webSocket.addEventListener('open', onOpen);
		webSocket.addEventListener('message', onMessage);
		webSocket.addEventListener('close', onClose);
		webSocket.addEventListener('error', onError);

		window.addEventListener('beforeunload', () => {
			webSocket.close();
			newContentNotifier.disconnectIntersectionObserver();
		});

		return webSocket;
	}

	function onOpen(event) {
		webSocket.send(JSON.stringify({ pathname }));
	}

	function onMessage(event) {
		// TODO: data variable will be used when WebSocket will eventually send HTML content
		const data = window.JSON.parse(event.data);

		notifyUserAboutNewContent();
	}

	function onClose(event) {
		if (event.reason) {
			console.warn(`Closing WebSocket, because of reason: "${event.reason}"`);
		}
	}

	function onError(event) {
		console.error('WebSocket error: ', event);
		newContentNotifier.disconnectIntersectionObserver();
	}

	function notifyUserAboutNewContent() {
		newContentNotifier.show();
	}

	function createNewContentNotifier() {
		const notifierTargetPlace = document.querySelector('.qa-part-q-list');
		const NOTIFIER_CLASSES = Object.freeze({
			BASE: 'qa-custom-new-content-notifier',
			VISIBLE: 'qa-custom-new-content-notifier--visible',
			ANIMATE: 'qa-custom-new-content-notifier--animate'
		});
		const { notifier, notifierCounter } = createNotifierDOM();
		const disconnectIntersectionObserver = observeNotifierIntersections(notifier);

		let notifierCounterValue = 0;

		return { show, hide, disconnectIntersectionObserver };

		function createNotifierDOM() {
			const notifier = document.createElement('button');
			notifier.title = 'Aktualizuj listę postów';
			notifier.classList.add(NOTIFIER_CLASSES.BASE);
			// TODO: remove this line after ensuring WebSockets work stable on production!
			notifier.style = 'display: none !important';
			notifier.innerHTML = `
				<span>Nowych postów: </span>
				<output></output>
			`;
			notifier.addEventListener('click', updatePageContent);

			notifierTargetPlace.insertAdjacentElement('beforebegin', notifier);

			const [ , notifierCounter ] = notifier.children;
			notifierCounter.addEventListener('animationend', () => {
				notifierCounter.classList.remove(NOTIFIER_CLASSES.ANIMATE);
			});

			return { notifier, notifierCounter };
		}

		function show() {
			notifier.classList.add(NOTIFIER_CLASSES.VISIBLE);
			incrementCounter();
		}

		function hide() {
			notifier.classList.remove(NOTIFIER_CLASSES.VISIBLE);
			resetCounter();
		}

		function incrementCounter() {
			notifierCounter.classList.add(NOTIFIER_CLASSES.ANIMATE);
			notifierCounter.textContent = ++notifierCounterValue;
		}

		function resetCounter() {
			notifierCounterValue = 0;
			notifierCounter.textContent = notifierCounterValue;
		}
	}

	function updatePageContent({ currentTarget }) {
		fetch(window.location.href)
			.then((response) => response.text())
			.then((html) => {
				const responseQuestionList =
						new DOMParser()
							.parseFromString(html, 'text/html')
							.querySelector(QA_Q_LIST_SELECTOR);

				showUpdatedPageContent(responseQuestionList.innerHTML, currentTarget);
			});
	}

	function showUpdatedPageContent(updatedContent, notifier) {
		questionList.innerHTML = updatedContent;
		newContentNotifier.hide();

		if (notifier.classList.contains('fixed-to-top')) {
			window.scroll({
				top: 0,
				behavior: 'smooth'
			});
		}
	}

	function observeNotifierIntersections(target) {
		const intersectionObserver = new IntersectionObserver(([entry]) => {
			target.classList.toggle('fixed-to-top', entry.intersectionRatio <= 0);
		});
		intersectionObserver.observe(target.previousElementSibling);

		return intersectionObserver.disconnect.bind(intersectionObserver);
	}
});
