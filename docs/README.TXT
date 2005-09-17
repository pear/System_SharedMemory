Project home page (Russian): http://bolk.exler.ru/files/shared/

Usage:

    include_once 'System/SharedMemory.php';

	$Shared =& System_SharedMemory::factory();

	$Shared->set('name', 'something');
	echo $Shared->get('name');

	$Shared->rm('name');