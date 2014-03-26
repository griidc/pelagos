package edu.tamucc.hri.griidc;

import java.io.IOException;
import java.sql.SQLException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

abstract public class SynchronizerBase {
	
	private boolean baseInitialized = false;
	protected RdbmsConnection risDbConnection = null;
	protected RdbmsConnection griidcDbConnection = null;
	
	public SynchronizerBase() {
		// TODO Auto-generated constructor stub
	}

	
	public boolean isBaseInitialized() {
		return baseInitialized;
	}


	public RdbmsConnection getRisDbConnection() {
		return risDbConnection;
	}


	public RdbmsConnection getGriidcDbConnection() {
		return griidcDbConnection;
	}


	abstract protected void initialize();
	
	public void commonInitialize()  {
		String cName = this.getClass().getName();
		if (!baseInitialized) {
			try {
				MiscUtils.openPrimaryLogFile();
				MiscUtils.openRisErrorLogFile();
				MiscUtils.openDeveloperReportFile();
			} catch (IOException e) {
				System.err.println("IOException in class: " + cName + " function: commonInitialize()");
				System.err.println("exception: " + e.getMessage());
				System.exit(-1);
			}
			try {
				this.risDbConnection = RdbmsUtils.getRisDbConnectionInstance();
				this.griidcDbConnection = RdbmsUtils
						.getGriidcDbConnectionInstance();
			} catch (SQLException e) {
				System.err.println("SQLException in class: " + cName + " function: commonInitialize()");
				System.err.println("exception: " + e.getMessage());
				System.exit(-1);
			}
			baseInitialized = true;
		}
	}
}
