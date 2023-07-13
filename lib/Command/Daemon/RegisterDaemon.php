<?php

declare(strict_types=1);

/**
 *
 * Nextcloud - App Ecosystem V2
 *
 * @copyright Copyright (c) 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\AppEcosystemV2\Command\Daemon;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use OCA\AppEcosystemV2\Service\DaemonConfigService;

class RegisterDaemon extends Command {
	private DaemonConfigService $daemonConfigService;

	public function __construct(DaemonConfigService $daemonConfigService) {
		parent::__construct();

		$this->daemonConfigService = $daemonConfigService;
	}

	protected function configure() {
		$this->setName('app_ecosystem_v2:daemon:register');
		$this->setDescription('Register daemon config for ExApp deployment');

		$this->addArgument('name', InputArgument::REQUIRED);
		$this->addArgument('display-name', InputArgument::REQUIRED);
		$this->addArgument('accepts-deploy-id', InputArgument::REQUIRED);
		$this->addArgument('protocol', InputArgument::REQUIRED);
		$this->addArgument('host', InputArgument::REQUIRED);
		$this->addArgument('nextcloud_url', InputArgument::REQUIRED);

		// daemon-config settings
		$this->addOption('net', null, InputOption::VALUE_REQUIRED, 'DeployConfig, docker network name');
		$this->addOption('host', null, InputOption::VALUE_REQUIRED, 'DeployConfig, docker daemon host (e.g. host.docker.internal)');

		$this->addUsage('local_docker "Docker local" "docker-install" "unix-socket" "/var/run/docker.sock" "http://nextcloud.local" --net "nextcloud" --host "host.docker.internal"');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$name = $input->getArgument('name');
		$displayName = $input->getArgument('display-name');
		$acceptsDeployId = $input->getArgument('accepts-deploy-id');
		$protocol = $input->getArgument('protocol');
		$host = $input->getArgument('host');
		$nextcloudUrl = $input->getArgument('nextcloud_url');

		$deployConfig = [
			'net' => $input->getOption('net') ?? 'host',
			'host' => $input->getOption('host'),
			'nextcloud_url' => $nextcloudUrl,
		];

		$daemonConfig = $this->daemonConfigService->registerDaemonConfig([
			'name' => $name,
			'display_name' => $displayName,
			'accepts_deploy_id' => $acceptsDeployId,
			'protocol' => $protocol,
			'host' => $host,
			'deploy_config' => $deployConfig,
		]);

		if ($daemonConfig === null) {
			$output->writeln('Failed to register daemon.');
			return 1;
		}

		$output->writeln('Daemon successfully registered.');
		return 0;
	}
}
